<?php

namespace Tests\Feature\EntityManagement;

use App\Models\Album;
use App\Models\Cancion;
use App\Models\Categoria;
use App\Models\Event;
use App\Models\Interprete;
use App\Models\News;
use App\Models\Organization;
use App\Models\OrganizationEntity;
use App\Models\OrganizationMember;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Con BACKOFFICE_ACCESS_BY_ENTITIES activo, el permiso global del rol colaborador ya no ensancha el
 * alcance: se elige y se carga solo sobre los artistas que se administran (spec member-selection-scoping).
 */
class MemberSelectionScopingTest extends TestCase
{
    use DatabaseTransactions;

    private Interprete $mine;
    private Interprete $foreign;
    private Interprete $guest;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        config(['backoffice_access.enabled' => true, 'responsecache.enabled' => false, 'responsecache.cache_store' => 'array']);
        Queue::fake();
        Http::fake();
        Storage::fake('public');
        Storage::fake('local');

        $this->mine = $this->artist('MiArtistaPropio');
        $this->foreign = $this->artist('ArtistaAjenoUno');
        $this->guest = $this->artist('InvitadoAjenoDos');

        Role::findOrCreate('colaborador', 'web');
        $this->user = User::factory()->create();
        $this->user->assignRole('colaborador');

        $org = Organization::create(['type' => 'artista', 'name' => 'Org '.uniqid(), 'status' => 'active']);
        OrganizationEntity::create(['organization_id' => $org->id, 'entity_type' => $this->mine->getMorphClass(), 'entity_id' => $this->mine->id, 'is_primary' => true]);
        OrganizationMember::create(['organization_id' => $org->id, 'user_id' => $this->user->id, 'role' => 'editor', 'status' => 'active']);
    }

    private function artist(string $name): Interprete
    {
        return Interprete::create(['interprete' => $name, 'slug' => Str::slug($name).'-'.uniqid(), 'biografia' => 'Bio de prueba.', 'estado' => 1, 'user_id' => $this->baseUserId()]);
    }

    private function admin(): User
    {
        $admin = User::factory()->create();
        $admin->assignRole(Role::findOrCreate('administrador', 'web'));

        return $admin;
    }

    private function selectBlock(string $html, string $id): string
    {
        $start = strpos($html, 'id="'.$id.'"') ?: strpos($html, 'name="'.$id.'"');
        $this->assertNotFalse($start, "no se encontró el select $id");

        return substr($html, $start, strpos($html, '</select>', $start) - $start);
    }

    // ---------------- Discos y canciones ----------------

    public function test_disc_and_song_forms_only_offer_my_artists(): void
    {
        $this->actingAs($this->user);

        foreach (['backend.discos.create', 'backend.canciones.create'] as $route) {
            $this->get(route($route))->assertOk()->assertSee('MiArtistaPropio')->assertDontSee('ArtistaAjenoUno')->assertDontSee('InvitadoAjenoDos');
        }
    }

    public function test_disc_and_song_creation_for_a_foreign_artist_is_rejected_server_side(): void
    {
        $this->actingAs($this->user);

        $this->post(route('backend.discos.store'), ['album' => 'Disco ajeno', 'anio' => '2024', 'interprete_id' => $this->foreign->id])->assertSessionHasErrors('interprete_id');
        $this->post(route('backend.canciones.store'), ['cancion' => 'Tema ajeno', 'interprete_id' => $this->foreign->id])->assertSessionHasErrors('interprete_id');
        $this->postJson(route('backend.canciones.store-ajax'), ['cancion' => 'Rápido ajeno', 'interprete_id' => $this->foreign->id])->assertForbidden();
        $this->assertSame(0, Album::where('album', 'Disco ajeno')->count() + Cancion::where('cancion', 'like', '%ajeno')->count());

        $this->post(route('backend.discos.store'), ['album' => 'Disco propio', 'anio' => '2024', 'interprete_id' => $this->mine->id])->assertSessionHasNoErrors();
        $this->postJson(route('backend.canciones.store-ajax'), ['cancion' => 'Rápido propio', 'interprete_id' => $this->mine->id])->assertOk();
    }

    public function test_a_previous_own_record_for_a_foreign_artist_stays_editable_but_cannot_be_moved_to_another_foreign_artist(): void
    {
        $legacy = Album::create(['album' => 'Disco antiguo', 'slug' => 'antiguo-'.uniqid(), 'anio' => '2020', 'interprete_id' => $this->foreign->id, 'user_id' => $this->user->id, 'estado' => 0, 'visitas' => 0]);
        $this->actingAs($this->user);

        $html = $this->get(route('backend.discos.edit', $legacy))->assertOk()->getContent();
        $select = $this->selectBlock($html, 'interprete_id');
        $this->assertStringContainsString('ArtistaAjenoUno', $select, 'el artista actual del registro propio sigue visible');
        $this->assertStringNotContainsString('InvitadoAjenoDos', $select);

        $this->put(route('backend.discos.update', $legacy), ['album' => 'Disco antiguo editado', 'anio' => '2020', 'interprete_id' => $this->foreign->id])->assertSessionHasNoErrors();
        $this->put(route('backend.discos.update', $legacy), ['album' => 'Disco antiguo', 'anio' => '2020', 'interprete_id' => $this->guest->id])->assertSessionHasErrors('interprete_id');
        $this->assertSame($this->foreign->id, (int) $legacy->fresh()->interprete_id);
    }

    // ---------------- Listado de Interpretes ----------------

    public function test_the_artists_listing_only_shows_my_fichas(): void
    {
        $this->actingAs($this->user)->get(route('backend.interpretes.index'))->assertOk()->assertSee('MiArtistaPropio')->assertDontSee('ArtistaAjenoUno');
    }

    // ---------------- Noticias ----------------

    private function newsPayload(array $extra = []): array
    {
        $title = 'Noticia '.uniqid();

        return array_merge([
            'titulo' => $title, 'slug' => Str::slug($title), 'noticia' => '<p>Cuerpo</p>', 'foto' => UploadedFile::fake()->image('n.jpg'),
            'categoria_id' => (Categoria::first() ?: Categoria::create(['nombre' => 'General', 'slug' => 'general']))->id, 'estado' => 0,
        ], $extra);
    }

    public function test_the_news_principal_must_be_mine_and_secondaries_can_be_guests(): void
    {
        $this->actingAs($this->user);

        $this->post(route('backend.news.store'), $this->newsPayload(['interprete_principal_id' => $this->foreign->id, 'interprete_secundarios' => [$this->mine->id]]))->assertSessionHasErrors('interprete_principal_id');
        $this->post(route('backend.news.store'), $this->newsPayload(['interprete_principal_id' => $this->foreign->id, 'interprete_secundarios' => [$this->guest->id]]))->assertSessionHasErrors('interprete_principal_id');
        $this->post(route('backend.news.store'), $this->newsPayload(['interprete_secundarios' => [$this->mine->id]]))->assertSessionHasErrors('interprete_principal_id');

        $title = 'Noticia valida '.uniqid();
        $this->post(route('backend.news.store'), $this->newsPayload(['titulo' => $title, 'slug' => Str::slug($title), 'interprete_principal_id' => $this->mine->id, 'interprete_secundarios' => [$this->foreign->id, $this->guest->id]]))
            ->assertSessionHasNoErrors();

        $news = News::where('title', $title)->firstOrFail();
        $this->assertSame($this->mine->id, (int) $news->interprete_id);
        $this->assertEqualsCanonicalizing([$this->foreign->id, $this->guest->id], $news->interpretes->pluck('id')->all());
    }

    public function test_the_news_form_limits_the_principal_but_not_the_secondaries(): void
    {
        $html = $this->actingAs($this->user)->get(route('backend.news.create'))->assertOk()->getContent();

        $principal = $this->selectBlock($html, 'interprete_principal_id');
        $secondary = $this->selectBlock($html, 'interprete_secundarios');

        $this->assertStringContainsString('MiArtistaPropio', $principal);
        $this->assertStringNotContainsString('ArtistaAjenoUno', $principal);
        $this->assertStringContainsString('required', substr($html, strpos($html, 'id="interprete_principal_id"'), 200));
        $this->assertStringContainsString('ArtistaAjenoUno', $secondary, 'los invitados se eligen entre todos');
        $this->assertStringContainsString('InvitadoAjenoDos', $secondary);
    }

    public function test_an_own_previous_news_keeps_its_principal_and_can_be_edited(): void
    {
        $category = Categoria::first() ?: Categoria::create(['nombre' => 'General', 'slug' => 'general']);
        $news = News::create(['title' => 'Noticia antigua', 'slug' => 'antigua-'.uniqid(), 'body' => '<p>x</p>', 'editorial_status' => 'draft', 'estado' => 0, 'created_by' => $this->user->id, 'interprete_id' => $this->foreign->id, 'categoria_id' => $category->id, 'published_at' => now()]);
        $this->actingAs($this->user);

        $html = $this->get(route('backend.news.edit', $news))->assertOk()->getContent();
        $this->assertStringContainsString('ArtistaAjenoUno', $this->selectBlock($html, 'interprete_principal_id'));

        $payload = fn (int $principal) => $this->newsPayload(['titulo' => 'Noticia antigua', 'slug' => $news->slug, 'foto' => null, 'interprete_principal_id' => $principal]);
        $this->put(route('backend.news.update', $news), $payload($this->foreign->id))->assertSessionHasNoErrors();
        $this->put(route('backend.news.update', $news), $payload($this->guest->id))->assertSessionHasErrors('interprete_principal_id');
    }

    // ---------------- Eventos ----------------

    private function eventPayload(array $artists, array $extra = []): array
    {
        return array_merge([
            'event_format' => 'single', 'title' => 'Evento '.uniqid(), 'body' => '<p>Detalle</p>', 'start_at' => now()->addWeek()->format('Y-m-d H:i:s'),
            'interprete_ids' => collect($artists)->pluck('id')->all(), 'estado' => 0,
        ], $extra);
    }

    public function test_events_must_include_one_of_my_artists_even_with_the_global_role_permission(): void
    {
        $this->actingAs($this->user);

        $this->post(route('backend.events.store'), $this->eventPayload([$this->foreign]))->assertSessionHasErrors('interprete_ids');
        $this->post(route('backend.events.store'), $this->eventPayload([]))->assertSessionHasErrors('interprete_ids');

        $data = $this->eventPayload([$this->mine, $this->foreign]);
        $this->post(route('backend.events.store'), $data)->assertSessionHasNoErrors();
        $this->assertTrue(Event::where('title', $data['title'])->exists());
    }

    public function test_multi_day_events_count_the_artists_of_their_sessions(): void
    {
        $this->actingAs($this->user);
        $payload = fn (array $firstArtists) => [
            'title' => 'Ciclo '.uniqid(), 'body' => '<p>Detalle</p>', 'event_format' => 'multiple', 'estado' => 0,
            'sessions' => [
                ['title' => 'Primera', 'start_at' => now()->addWeek()->toDateTimeString(), 'interprete_ids' => $firstArtists],
                ['title' => 'Segunda', 'start_at' => now()->addDays(8)->toDateTimeString(), 'interprete_ids' => [$this->guest->id]],
            ],
        ];

        $this->post(route('backend.events.store'), $payload([$this->foreign->id]))->assertSessionHasErrors('interprete_ids');
        $this->post(route('backend.events.store'), $payload([$this->mine->id]))->assertSessionHasNoErrors();
    }

    public function test_the_event_form_lists_my_artists_first_and_explains_the_rule(): void
    {
        $html = $this->actingAs($this->user)->get(route('backend.events.create'))->assertOk()->assertSee('Mis artistas')->assertSee('Otros artistas (invitados)')->assertSee('Incluí al menos un artista o festival que administrás')->getContent();

        $select = $this->selectBlock($html, 'interprete_ids');
        $this->assertLessThan(strpos($select, 'ArtistaAjenoUno'), strpos($select, 'MiArtistaPropio'));
        $this->assertStringContainsString('InvitadoAjenoDos', $select, 'los invitados se eligen entre todos');
    }

    // ---------------- Lo que no cambia ----------------

    public function test_administrators_are_never_restricted(): void
    {
        $this->actingAs($this->admin());

        $this->get(route('backend.discos.create'))->assertOk()->assertSee('ArtistaAjenoUno');
        $this->post(route('backend.discos.store'), ['album' => 'Disco admin', 'anio' => '2024', 'interprete_id' => $this->foreign->id])->assertSessionHasNoErrors();
        $this->post(route('backend.news.store'), $this->newsPayload())->assertSessionHasNoErrors();
        $this->post(route('backend.events.store'), $this->eventPayload([$this->foreign]))->assertSessionHasNoErrors();
        $this->get(route('backend.interpretes.index', ['search' => 'ArtistaAjenoUno']))->assertSee('ArtistaAjenoUno');
    }

    public function test_with_the_flag_off_nothing_changes_for_collaborators(): void
    {
        config(['backoffice_access.enabled' => false]);
        $this->actingAs($this->user);

        $this->get(route('backend.discos.create'))->assertOk()->assertSee('ArtistaAjenoUno');
        $this->post(route('backend.discos.store'), ['album' => 'Disco libre', 'anio' => '2024', 'interprete_id' => $this->foreign->id])->assertSessionHasNoErrors();
        $this->post(route('backend.news.store'), $this->newsPayload())->assertSessionHasNoErrors();
        $this->post(route('backend.events.store'), $this->eventPayload([$this->foreign]))->assertSessionHasNoErrors();
        $this->get(route('backend.interpretes.index', ['search' => 'ArtistaAjenoUno']))->assertSee('ArtistaAjenoUno');
    }
}
