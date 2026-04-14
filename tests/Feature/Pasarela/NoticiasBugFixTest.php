<?php

namespace Tests\Feature\Pasarela;

use App\Models\Interprete;
use App\Models\News;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * Tests para los bugs corregidos en el sistema de noticias:
 *
 * (a) admin/noticias: Call to undefined relationship [user] on model News
 * (b) interprete/noticias: compact(): Undefined variable $metaTitle
 * (c) /noticias-del-folklore-argentino: no retornaba noticias con editorial_status='published'
 */
class NoticiasBugFixTest extends TestCase
{
    use DatabaseTransactions;

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function makeAdminUser(): User
    {
        $user = User::factory()->create();
        // Asignar permiso de ver noticias (si usa spatie/laravel-permission)
        // Como puede no estar configurado en test, usamos admin o super-admin
        try {
            $user->assignRole('super-admin');
        } catch (\Exception $e) {
            // Si no hay roles configurados, la Policy puede estar deshabilitada en tests
        }
        return $user;
    }

    private function makePublishedNews(int $count = 1, array $extra = []): array
    {
        $items = [];
        for ($i = 0; $i < $count; $i++) {
            $items[] = News::create(array_merge([
                'title'            => 'Noticia test ' . uniqid(),
                'slug'             => 'noticia-test-' . uniqid(),
                'body'             => 'Contenido de prueba para test de noticias.',
                'categoria_id'     => 1,
                'editorial_status' => 'published',
                'estado'           => 1,
            ], $extra));
        }
        return $items;
    }

    // -------------------------------------------------------------------------
    // Fix (a): relación user en News (admin/noticias)
    // -------------------------------------------------------------------------

    /** @test */
    public function test_news_model_has_user_relation_alias(): void
    {
        $user = User::factory()->create();
        $news = News::create([
            'title'        => 'Test user relation ' . uniqid(),
            'slug'         => 'test-user-rel-' . uniqid(),
            'body'         => 'Contenido',
            'categoria_id' => 1,
            'created_by'   => $user->id,
        ]);

        // El alias user() debe resolver igual que creator()
        $this->assertNotNull($news->user);
        $this->assertEquals($user->id, $news->user->id);
        $this->assertEquals($user->id, $news->creator->id);
    }

    /** @test */
    public function test_news_eager_load_user_works(): void
    {
        $user = User::factory()->create();
        News::create([
            'title'        => 'Test eager user ' . uniqid(),
            'slug'         => 'test-eager-user-' . uniqid(),
            'body'         => 'Contenido',
            'categoria_id' => 1,
            'created_by'   => $user->id,
        ]);

        // La consulta que usa el NoticiaController@index no debe tirar excepción
        $noticias = \App\Models\Noticia::with(['interpretes', 'user', 'categoria', 'images'])
            ->orderBy('published_at', 'desc')
            ->get();

        $this->assertNotNull($noticias);
    }

    /** @test */
    public function test_noticia_index_query_uses_correct_relation_names(): void
    {
        // Verifica que la consulta que usa NoticiaController@index no falla
        // (antes fallaba con "Call to undefined relationship [user]")
        $user = User::factory()->create();
        News::create([
            'title'        => 'Test query ' . uniqid(),
            'slug'         => 'test-query-' . uniqid(),
            'body'         => 'Contenido',
            'categoria_id' => 1,
            'created_by'   => $user->id,
        ]);

        // Esta es exactamente la consulta de NoticiaController@index — no debe tirar excepción
        $noticias = \App\Models\Noticia::with([
            'interpretes:id,interprete',
            'user:id,name',
            'categoria:id,nombre',
            'images',
        ])->orderBy('published_at', 'desc')->get();

        $this->assertNotNull($noticias);
        $item = $noticias->first();
        if ($item) {
            $this->assertNotNull($item->user);
        }
    }

    // -------------------------------------------------------------------------
    // Fix (b): $metaTitle en interprete/noticias
    // -------------------------------------------------------------------------

    /** @test */
    public function test_artista_noticias_page_has_meta_title(): void
    {
        $interprete = Interprete::where('estado', 1)->first();

        if (!$interprete) {
            $this->markTestSkipped('No hay intérpretes activos en la BD de test.');
        }

        $response = $this->get(route('artista.noticias', $interprete->slug));

        // Debe cargar sin error 500 (antes fallaba con Undefined variable $metaTitle)
        $response->assertStatus(200);
    }

    /** @test */
    public function test_noticias_controller_noticias_method_defines_meta_title(): void
    {
        $interprete = Interprete::where('estado', 1)->first();

        if (!$interprete) {
            $this->markTestSkipped('No hay intérpretes activos en la BD de test.');
        }

        // Crear una noticia publicada para ese intérprete
        $news = News::create([
            'title'            => 'Noticia artista test ' . uniqid(),
            'slug'             => 'noticia-artista-' . uniqid(),
            'body'             => 'Contenido de prueba.',
            'categoria_id'     => 1,
            'interprete_id'    => $interprete->id,
            'editorial_status' => 'published',
            'estado'           => 1,
        ]);

        $response = $this->get(route('artista.noticias', $interprete->slug));
        $response->assertStatus(200);

        // La vista recibe $metaTitle — verificar que el HTML lo contiene
        $response->assertSee($interprete->interprete);
    }

    // -------------------------------------------------------------------------
    // Fix (c): /noticias-del-folklore-argentino filtra por 'published'
    // -------------------------------------------------------------------------

    /** @test */
    public function test_public_news_index_shows_published_news(): void
    {
        // Crear noticias con editorial_status='published'
        $this->makePublishedNews(2);

        $response = $this->get(route('noticias.index'));

        $response->assertOk();
        // La página debe renderizar sin errores y mostrar contenido
        $response->assertViewHas('ultimas');
        $this->assertGreaterThan(0, $response->viewData('ultimas')->count(),
            'El listado público debe mostrar noticias con editorial_status=published');
    }

    /** @test */
    public function test_public_news_index_does_not_show_draft_news(): void
    {
        $slug = 'noticia-draft-' . uniqid();
        News::create([
            'title'            => 'Noticia draft ' . uniqid(),
            'slug'             => $slug,
            'body'             => 'Contenido borrador.',
            'categoria_id'     => 1,
            'editorial_status' => 'draft',
            'estado'           => 0,
        ]);

        $response = $this->get(route('noticias.index'));
        $response->assertOk();

        // La noticia draft no debe aparecer en el listado
        $slugsEnVista = $response->viewData('ultimas')->pluck('slug')->toArray();
        $this->assertNotContains($slug, $slugsEnVista);
    }

    /** @test */
    public function test_public_news_index_does_not_show_approved_but_not_published(): void
    {
        $slug = 'noticia-aprobada-no-publicada-' . uniqid();
        News::create([
            'title'            => 'Noticia aprobada ' . uniqid(),
            'slug'             => $slug,
            'body'             => 'Aprobada pero no publicada al portal.',
            'categoria_id'     => 1,
            'editorial_status' => 'approved',
            'estado'           => 0,
        ]);

        $response = $this->get(route('noticias.index'));
        $response->assertOk();

        // 'approved' no es 'published' — no debe aparecer en el portal
        $slugsEnVista = $response->viewData('ultimas')->pluck('slug')->toArray();
        $this->assertNotContains($slug, $slugsEnVista);
    }

    /** @test */
    public function test_news_model_user_id_mutator_maps_to_created_by(): void
    {
        $user = User::factory()->create();

        $news = new News();
        $news->title = 'Test mutator ' . uniqid();
        $news->slug  = 'test-mutator-' . uniqid();
        $news->body  = 'Contenido';
        $news->categoria_id = 1;
        $news->user_id = $user->id; // via compat mutator

        $this->assertEquals($user->id, $news->created_by,
            'El mutator user_id debe redirigir a created_by');
    }

    /** @test */
    public function test_news_model_publicar_mutator_maps_to_published_at(): void
    {
        $news = new News();
        $news->publicar = '2026-04-14';

        // published_at tiene cast datetime → devuelve Carbon; comparamos la fecha formateada
        $this->assertEquals('2026-04-14', \Carbon\Carbon::parse($news->published_at)->format('Y-m-d'),
            'El mutator publicar debe redirigir a published_at');
    }
}
