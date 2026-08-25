<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Comida;
use App\Models\Event;
use App\Models\Festival;
use App\Models\Interprete;
use App\Models\KnowledgeCategory;
use App\Models\Mes;
use App\Models\Mito;
use App\Models\Provincia;
use App\Models\User;
use App\Services\EventService;
use App\Services\NewsService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RichTextHeadingBackendNormalizationTest extends TestCase
{
    use DatabaseTransactions;

    private function makeAdminUser(): User
    {
        Role::findOrCreate('administrador', 'web');

        $user = User::factory()->create();
        $user->assignRole('administrador');

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
    public function editorial_forms_limit_body_headings_to_h2_and_h3(): void
    {
        $admin = $this->makeAdminUser();
        $this->actingAs($admin);

        $festival = Festival::create([
            'title' => 'Festival Perfil',
            'slug' => 'festival-perfil',
            'body' => '<p>Body</p>',
            'province_id' => $this->province()->id,
            'mes_id' => $this->month()->id,
            'user_id' => $admin->id,
            'status' => 'draft',
        ]);

        $interprete = Interprete::create([
            'interprete' => 'Perfil artista',
            'slug' => 'perfil-artista',
            'biografia' => '<p>Bio</p>',
            'estado' => 1,
            'user_id' => $admin->id,
        ]);

        $mito = Mito::create([
            'titulo' => 'Perfil mito',
            'slug' => 'perfil-mito',
            'mito' => '<p>Mito</p>',
            'foto' => 'perfil-mito.jpg',
            'visitas' => 0,
            'publicar' => now(),
            'estado' => 1,
            'user_id' => $admin->id,
        ]);

        $comida = Comida::create([
            'titulo' => 'Perfil comida',
            'slug' => 'perfil-comida',
            'receta' => '<p>Receta</p>',
            'foto' => 'perfil-comida.jpg',
            'visitas' => 0,
            'publicar' => now(),
            'estado' => 1,
            'user_id' => $admin->id,
        ]);

        $event = Event::create([
            'title' => 'Perfil evento',
            'slug' => 'perfil-evento',
            'body' => '<p>Detalle</p>',
            'start_at' => now()->addDay(),
            'editorial_status' => 'draft',
            'created_by' => $admin->id,
        ]);

        KnowledgeCategory::factory()->create([
            'name' => 'Ritmos',
            'slug' => 'ritmos',
            'is_active' => true,
        ]);

        $this->get(route('backend.news.create'))
            ->assertOk()
            ->assertSee('data-ckeditor-profile="editorial-body"', false)
            ->assertSee("view: 'h2'", false)
            ->assertSee("view: 'h3'", false)
            ->assertDontSee("view: 'h1'", false);

        $this->get(route('backend.festivales.edit', $festival))
            ->assertOk()
            ->assertSee('data-ckeditor-profile="editorial-body"', false);

        $this->get(route('backend.knowledge-articles.create'))
            ->assertOk()
            ->assertSee('data-ckeditor-profile="editorial-body"', false);

        $this->get(route('backend.interpretes.edit', $interprete))
            ->assertOk()
            ->assertSee('data-ckeditor-profile="editorial-body"', false);

        $this->get(route('backend.mitos.edit', $mito))
            ->assertOk()
            ->assertSee('data-ckeditor-profile="editorial-body"', false);

        $this->get(route('backend.comidas.edit', $comida))
            ->assertOk()
            ->assertSee('data-ckeditor-profile="editorial-body"', false);

        $this->get(route('backend.events.edit', $event))
            ->assertOk()
            ->assertSee('data-summernote-profile="editorial-body"', false)
            ->assertSee("tag: 'h2'", false)
            ->assertSee("tag: 'h3'", false)
            ->assertDontSee("tag: 'h1'", false);
    }

    /** @test */
    public function services_and_backend_forms_normalize_h1_to_h2(): void
    {
        $admin = $this->makeAdminUser();
        $this->actingAs($admin);

        $news = app(NewsService::class)->createNews([
            'titulo' => 'Noticia headings',
            'slug' => 'noticia-headings',
            'noticia' => '<h1>Encabezado</h1><p>Texto.</p>',
            'categoria_id' => $this->category()->id,
            'estado' => 0,
        ]);
        $this->assertSame('<h2>Encabezado</h2><p>Texto.</p>', $news->fresh()->body);

        $event = app(EventService::class)->createEvent([
            'title' => 'Evento headings',
            'body' => '<h1>Agenda</h1><p>Texto.</p>',
            'start_at' => now()->addDay()->toDateTimeString(),
            'estado' => 0,
        ]);
        $this->assertSame('<h2>Agenda</h2><p>Texto.</p>', $event->fresh()->body);

        $festival = Festival::create([
            'title' => 'Festival headings',
            'slug' => 'festival-headings',
            'body' => '<p>Inicial</p>',
            'province_id' => $this->province()->id,
            'mes_id' => $this->month()->id,
            'user_id' => $admin->id,
            'status' => 'draft',
        ]);

        $this->put(route('backend.festivales.update', $festival), [
            'title' => 'Festival headings',
            'body' => '<h1>Festival</h1><p>Texto.</p>',
            'province_id' => $this->province()->id,
            'mes_id' => $this->month()->id,
            'published_at' => now()->toDateString(),
            'status' => 'draft',
        ])->assertRedirect(route('backend.festivales.index'));
        $this->assertSame('<h2>Festival</h2><p>Texto.</p>', $festival->fresh()->body);

        $interprete = Interprete::create([
            'interprete' => 'Artista headings',
            'slug' => 'artista-headings',
            'biografia' => '<p>Inicial</p>',
            'estado' => 0,
            'user_id' => $admin->id,
        ]);

        $this->put(route('backend.interpretes.update', $interprete), [
            'interprete' => 'Artista headings',
            'biografia' => '<h1>Biografia</h1><p>Texto.</p>',
        ])->assertRedirect(route('backend.interpretes.index'));
        $this->assertSame('<h2>Biografia</h2><p>Texto.</p>', $interprete->fresh()->biografia);

        $mito = Mito::create([
            'titulo' => 'Mito headings',
            'slug' => 'mito-headings',
            'mito' => '<p>Inicial</p>',
            'foto' => 'mito-headings.jpg',
            'visitas' => 0,
            'publicar' => now(),
            'estado' => 1,
            'user_id' => $admin->id,
        ]);

        $this->put(route('backend.mitos.update', $mito), [
            'titulo' => 'Mito headings',
            'mito' => '<h1>Mito</h1><p>Texto.</p>',
            'publicar' => now()->toDateTimeString(),
        ])->assertRedirect(route('backend.mitos.index'));
        $this->assertSame('<h2>Mito</h2><p>Texto.</p>', $mito->fresh()->mito);

        $comida = Comida::create([
            'titulo' => 'Comida headings',
            'slug' => 'comida-headings',
            'receta' => '<p>Inicial</p>',
            'foto' => 'comida-headings.jpg',
            'visitas' => 0,
            'publicar' => now(),
            'estado' => 1,
            'user_id' => $admin->id,
        ]);

        $this->put(route('backend.comidas.update', $comida), [
            'titulo' => 'Comida headings',
            'receta' => '<h1>Receta</h1><p>Texto.</p>',
            'publicar' => now()->toDateTimeString(),
        ])->assertRedirect(route('backend.comidas.index'));
        $this->assertSame('<h2>Receta</h2><p>Texto.</p>', $comida->fresh()->receta);
    }
}
