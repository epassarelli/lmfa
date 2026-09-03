<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Event;
use App\Models\Festival;
use App\Models\Interprete;
use App\Models\Mes;
use App\Models\News;
use App\Models\Provincia;
use App\Models\User;
use App\Services\EventService;
use App\Services\NewsService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminAutoPublishBackendTest extends TestCase
{
    use DatabaseTransactions;

    private function makeAdminUser(): User
    {
        Role::findOrCreate('administrador', 'web');

        $user = User::factory()->create();
        $user->assignRole('administrador');

        return $user;
    }

    private function makeRegularUser(): User
    {
        Role::findOrCreate('colaborador', 'web');

        $user = User::factory()->create();
        $user->assignRole('colaborador');

        return $user;
    }

    private function category(): Categoria
    {
        return Categoria::first() ?: Categoria::create([
            'nombre' => 'General',
            'slug' => 'general',
        ]);
    }

    private function province(): Provincia
    {
        return Provincia::first() ?: Provincia::create([
            'nombre' => 'Cordoba',
        ]);
    }

    private function createInterprete(string $name, string $slug): Interprete
    {
        return Interprete::create([
            'interprete' => $name,
            'slug' => $slug,
            'biografia' => 'Biografia de prueba para el artista.',
            'estado' => 1,
            'user_id' => auth()->id(),
        ]);
    }

    private function month(): Mes
    {
        return Mes::first() ?: Mes::create([
            'nombre' => 'Enero',
        ]);
    }

    /** @test */
    public function admin_created_news_can_remain_draft_for_editorial_review(): void
    {
        $admin = $this->makeAdminUser();
        $this->actingAs($admin);

        $news = app(NewsService::class)->createNews([
            'titulo' => 'News admin create',
            'slug' => 'news-admin-create',
            'noticia' => 'Contenido',
            'categoria_id' => $this->category()->id,
            'estado' => 0,
        ]);

        $this->assertSame('draft', $news->fresh()->editorial_status);
        $this->assertNull($news->fresh()->published_at);
    }

    /** @test */
    public function admin_updated_news_can_remain_draft_for_editorial_review(): void
    {
        $admin = $this->makeAdminUser();
        $this->actingAs($admin);

        $news = News::create([
            'title' => 'News draft',
            'slug' => 'news-draft-admin-update',
            'body' => 'Contenido',
            'categoria_id' => $this->category()->id,
            'editorial_status' => 'draft',
            'estado' => 0,
            'created_by' => $admin->id,
        ]);

        app(NewsService::class)->updateNews($news, [
            'titulo' => 'News draft updated',
            'slug' => 'news-draft-admin-update',
            'noticia' => 'Contenido actualizado',
            'categoria_id' => $this->category()->id,
            'estado' => 0,
        ]);

        $this->assertSame('draft', $news->fresh()->editorial_status);
        $this->assertNull($news->fresh()->published_at);
    }

    /** @test */
    public function admin_created_event_can_remain_draft_for_editorial_review(): void
    {
        $admin = $this->makeAdminUser();
        $this->actingAs($admin);

        $event = app(EventService::class)->createEvent([
            'title' => 'Event admin create',
            'body' => 'Detalle',
            'start_at' => now()->addDay()->toDateTimeString(),
            'estado' => 0,
        ]);

        $this->assertSame('draft', $event->fresh()->editorial_status);
        $this->assertNull($event->fresh()->published_at);
    }

    /** @test */
    public function admin_updated_event_can_remain_draft_for_editorial_review(): void
    {
        $admin = $this->makeAdminUser();
        $this->actingAs($admin);

        $event = Event::create([
            'title' => 'Event draft',
            'body' => 'Detalle',
            'slug' => 'event-draft-admin-update',
            'start_at' => now()->addDay(),
            'editorial_status' => 'draft',
            'created_by' => $admin->id,
        ]);

        app(EventService::class)->updateEvent($event, [
            'title' => 'Event draft updated',
            'body' => 'Detalle actualizado',
            'slug' => 'event-draft-admin-update',
            'start_at' => now()->addDays(2)->toDateTimeString(),
            'estado' => 0,
        ]);

        $this->assertSame('draft', $event->fresh()->editorial_status);
        $this->assertNull($event->fresh()->published_at);
    }

    /** @test */
    public function event_service_syncs_multiple_artists_and_replaces_the_previous_lineup(): void
    {
        $admin = $this->makeAdminUser();
        $this->actingAs($admin);
        $first = $this->createInterprete('Primer artista evento', 'primer-artista-evento');
        $second = $this->createInterprete('Segundo artista evento', 'segundo-artista-evento');
        $third = $this->createInterprete('Tercer artista evento', 'tercer-artista-evento');

        $event = app(EventService::class)->createEvent([
            'title' => 'Evento con cartel completo',
            'body' => 'Detalle',
            'start_at' => now()->addDay()->toDateTimeString(),
            'interprete_ids' => [$first->id, $second->id],
        ]);

        $this->assertEqualsCanonicalizing([$first->id, $second->id], $event->interpretes()->pluck('interpretes.id')->all());

        app(EventService::class)->updateEvent($event, [
            'title' => 'Evento con cartel actualizado',
            'body' => 'Detalle',
            'start_at' => now()->addDays(2)->toDateTimeString(),
            'interprete_ids' => [$third->id],
        ]);

        $this->assertSame([$third->id], $event->fresh()->interpretes()->pluck('interpretes.id')->all());
    }

    /** @test */
    public function admin_updating_festival_sets_it_active(): void
    {
        $admin = $this->makeAdminUser();
        $this->actingAs($admin);

        $festival = Festival::create([
            'title' => 'Festival pendiente',
            'slug' => 'festival-pendiente',
            'body' => 'Detalle',
            'province_id' => $this->province()->id,
            'mes_id' => $this->month()->id,
            'user_id' => $admin->id,
            'published_at' => now(),
            'status' => 'draft',
        ]);

        $response = $this->put(route('backend.festivales.update', $festival), [
            'title' => 'Festival actualizado',
            'body' => 'Detalle actualizado',
            'province_id' => $this->province()->id,
            'mes_id' => $this->month()->id,
            'published_at' => now()->toDateString(),
            'status' => 'published',
        ]);

        $response->assertRedirect(route('backend.festivales.index'));
        $this->assertSame('published', $festival->fresh()->status);
    }

    /** @test */
    public function admin_updating_interprete_sets_it_active(): void
    {
        $admin = $this->makeAdminUser();
        $this->actingAs($admin);

        $interprete = Interprete::create([
            'interprete' => 'Artista pendiente',
            'slug' => 'artista-pendiente',
            'biografia' => 'Bio',
            'estado' => 0,
            'user_id' => $admin->id,
        ]);

        $response = $this->put(route('backend.interpretes.update', $interprete), [
            'interprete' => 'Artista actualizado',
            'biografia' => 'Bio actualizada',
        ]);

        $response->assertRedirect(route('backend.interpretes.index'));
        $this->assertTrue($interprete->fresh()->estado);
    }

    /** @test */
    public function non_admin_news_flow_keeps_existing_draft_behavior(): void
    {
        $user = $this->makeRegularUser();
        $this->actingAs($user);

        $news = app(NewsService::class)->createNews([
            'titulo' => 'News regular create',
            'slug' => 'news-regular-create',
            'noticia' => 'Contenido',
            'categoria_id' => $this->category()->id,
            'estado' => 1,
        ]);

        $this->assertSame('draft', $news->fresh()->editorial_status);
    }
}
