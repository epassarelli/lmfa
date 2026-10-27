<?php

namespace Tests\Feature\Backend;

use App\Models\Album;
use App\Models\Cancion;
use App\Models\Event;
use App\Models\Festival;
use App\Models\Interprete;
use App\Models\Mes;
use App\Models\User;
use App\Support\ModerationQueue;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ModerationCenterTest extends TestCase
{
    use DatabaseTransactions;

    private function admin(): User
    {
        $user = User::factory()->create();
        Role::findOrCreate('administrador', 'web');
        $user->assignRole('administrador');

        return $user;
    }

    /** Vacía las colas dentro de la transacción del test (sin eventos de modelo) para no depender de datos previos. */
    private function clearQueues(): void
    {
        foreach (['news', 'events', 'programs', 'venues', 'interpretes', 'albums', 'canciones', 'festivales'] as $queue) {
            ModerationQueue::$queue()->toBase()->delete();
        }
    }

    private function festival(string $title): Festival
    {
        return Festival::create([
            'title' => $title, 'slug' => 'f-'.uniqid(), 'status' => 'draft',
            'mes_id' => (Mes::first() ?: Mes::create(['nombre' => 'Enero']))->id,
        ]);
    }

    private function seedOnePerType(User $author): void
    {
        Event::create(['title' => 'Evento QA', 'slug' => 'e-'.uniqid(), 'body' => '<p>x</p>', 'start_at' => now()->addWeek(), 'editorial_status' => 'draft', 'status' => 'active', 'modality' => 'presencial', 'created_by' => $author->id]);
        $artist = Interprete::create(['interprete' => 'Artista QA', 'slug' => 'a-'.uniqid(), 'biografia' => '<p>b</p>', 'estado' => 0, 'user_id' => $author->id]);
        Album::create(['album' => 'Disco QA', 'slug' => 'd-'.uniqid(), 'anio' => '2024', 'interprete_id' => $artist->id, 'user_id' => $author->id, 'estado' => 0, 'visitas' => 0]);
        Cancion::create(['cancion' => 'Cancion QA', 'slug' => 'c-'.uniqid(), 'letra' => 'x', 'visitas' => 0, 'estado' => 0, 'user_id' => $author->id]);
        $this->festival('Festival QA');
    }

    public function test_cards_follow_the_priority_order_and_start_collapsed(): void
    {
        $admin = $this->admin();
        $this->clearQueues();
        $this->seedOnePerType($admin);

        $response = $this->actingAs($admin)->get(route('backend.moderation.index'))->assertOk();

        $response->assertSeeInOrder([
            'Eventos Pendientes', 'Intérpretes Pendientes', 'Discos Pendientes', 'Canciones Pendientes', 'Festivales en Borrador',
        ]);
        $this->assertSame(5, substr_count($response->getContent(), 'card-outline collapsed-card'));
        $this->assertSame(5, substr_count($response->getContent(), 'data-card-widget="collapse"'));
    }

    public function test_cards_without_pending_items_are_not_rendered(): void
    {
        $admin = $this->admin();
        $this->clearQueues();
        $this->festival('Solo festival');

        $this->actingAs($admin)->get(route('backend.moderation.index'))->assertOk()
            ->assertSee('Festivales en Borrador')
            ->assertDontSee('Noticias Pendientes')
            ->assertDontSee('Eventos Pendientes')
            ->assertDontSee('Lugares Propuestos')
            ->assertDontSee('No hay contenido pendiente de moderación');
    }

    public function test_an_empty_queue_shows_a_single_notice(): void
    {
        $admin = $this->admin();
        $this->clearQueues();

        $this->actingAs($admin)->get(route('backend.moderation.index'))->assertOk()
            ->assertSee('No hay contenido pendiente de moderación')
            ->assertDontSee('data-card-widget="collapse"', false);
    }

    public function test_festivals_use_the_news_table_layout_with_province(): void
    {
        $admin = $this->admin();
        $this->clearQueues();
        $this->festival('Festival con tabla');

        $this->actingAs($admin)->get(route('backend.moderation.index'))->assertOk()
            ->assertSeeInOrder(['<th>Título</th>', '<th>Autor</th>', '<th>Provincia</th>', '<th>Fecha Carga</th>', 'Acciones'], false)
            ->assertSee('Festival con tabla')
            ->assertSee('Publicar');
    }

    public function test_publishing_keeps_the_card_open_for_the_next_item(): void
    {
        $admin = $this->admin();
        $this->clearQueues();
        $first = $this->festival('Primero');
        $this->festival('Segundo');

        $this->actingAs($admin)->post(route('backend.moderation.publish', ['type' => 'festival', 'id' => $first->id]))
            ->assertRedirect(route('backend.moderation.index'))
            ->assertSessionHas('open', 'festivales');

        $html = $this->actingAs($admin)->withSession(['open' => 'festivales'])->get(route('backend.moderation.index'))->assertOk()->getContent();

        $this->assertStringContainsString('id="mod-festivales"', $html);
        $this->assertStringNotContainsString('card-outline collapsed-card', $html);
    }

    public function test_non_administrators_cannot_open_the_center(): void
    {
        $user = User::factory()->create();
        Role::findOrCreate('colaborador', 'web');
        $user->assignRole('colaborador');

        $this->actingAs($user)->get(route('backend.moderation.index'))->assertForbidden();
    }
}
