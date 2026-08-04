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
    public function admin_updating_festival_sets_it_active(): void
    {
        $admin = $this->makeAdminUser();
        $this->actingAs($admin);

        $festival = Festival::create([
            'titulo' => 'Festival pendiente',
            'slug' => 'festival-pendiente',
            'detalle' => 'Detalle',
            'provincia_id' => $this->province()->id,
            'mes_id' => $this->month()->id,
            'user_id' => $admin->id,
            'publicar' => now(),
            'estado' => 0,
        ]);

        $response = $this->put(route('backend.festivales.update', $festival), [
            'titulo' => 'Festival actualizado',
            'detalle' => 'Detalle actualizado',
            'provincia_id' => $this->province()->id,
            'mes_id' => $this->month()->id,
            'publicar' => now()->toDateString(),
        ]);

        $response->assertRedirect(route('backend.festivales.index'));
        $this->assertSame(1, $festival->fresh()->estado);
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
        $this->assertSame(1, $interprete->fresh()->estado);
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
