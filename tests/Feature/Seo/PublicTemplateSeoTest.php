<?php

namespace Tests\Feature\Seo;

use App\Models\KnowledgeArticle;
use App\Models\KnowledgeCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PublicTemplateSeoTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function home_renders_canonical_title_description_and_single_h1(): void
    {
        $response = $this->call('GET', '/', [], [], [], $this->serverVariables());

        $response->assertOk();
        $response->assertSee('<title>Mi Folklore Argentino | Tradiciones, musica y cultura popular</title>', false);
        $response->assertSee('<meta name="description" content="Portal editorial sobre folklore argentino con noticias, artistas, letras, discos, festivales y contenidos de consulta permanente.">', false);
        $response->assertSee('<link rel="canonical" href="https://mifolkloreargentino.com/" />', false);
        $response->assertSee('<h1 class="mb-4 border-b-2 border-[#ff661f] text-xl font-semibold text-gray-900">Mi Folklore Argentino</h1>', false);
        $this->assertSame(1, substr_count($response->getContent(), '<h1'));
    }

    /** @test */
    public function artist_and_biography_pages_generate_clean_metadata_from_biography(): void
    {
        DB::table('interpretes')->insert([
            'id' => 1201,
            'interprete' => 'Raul Carnota',
            'slug' => 'raul-carnota',
            'biografia' => '<p>Raul Carnota fue un referente del folklore argentino. <strong>Compuso</strong> obras claves y marco una epoca.</p>',
            'estado' => 1,
            'user_id' => null,
            'visitas' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $artist = $this->call('GET', '/raul-carnota', [], [], [], $this->serverVariables());
        $artist->assertOk();
        $artist->assertSee('<title>Biografia de Raul Carnota | Folklore Argentino</title>', false);
        $artist->assertSee('content="Raul Carnota fue un referente del folklore argentino. Compuso obras claves y marco una epoca."', false);
        $artist->assertSee('<link rel="canonical" href="https://mifolkloreargentino.com/raul-carnota" />', false);
        $artist->assertSee('<h1 class="fw-bold font-bold pb-4 text-3xl text-gray-800">Raul Carnota</h1>', false);

        $bio = $this->call('GET', '/raul-carnota/biografia', [], [], [], $this->serverVariables());
        $bio->assertOk();
        $bio->assertSee('<title>Biografia de Raul Carnota | Folklore Argentino</title>', false);
        $bio->assertSee('content="Raul Carnota fue un referente del folklore argentino. Compuso obras claves y marco una epoca."', false);
        $bio->assertSee('<link rel="canonical" href="https://mifolkloreargentino.com/raul-carnota/biografia" />', false);
        $bio->assertSee('<h1 class="text-2xl font-semibold mb-2">Biografia de Raul Carnota</h1>', false);
        $bio->assertSee('data-nosnippet', false);
        $this->assertSame(1, substr_count($bio->getContent(), '<h1'));
    }

    /** @test */
    public function news_page_prefers_explicit_seo_fields_and_keeps_canonical_without_www(): void
    {
        $categoriaId = DB::table('categorias')->insertGetId([
            'nombre' => 'Actualidad',
            'slug' => 'actualidad',
            'metetittle' => 'Actualidad',
            'metadescription' => 'Actualidad',
        ]);

        DB::table('news')->insert([
            'id' => 2101,
            'title' => 'Titulo visible noticia',
            'slug' => 'titulo-visible-noticia',
            'body' => '<p>Texto legacy con <strong>HTML</strong> y saltos.</p><p>Segundo parrafo.</p>',
            'categoria_id' => $categoriaId,
            'editorial_status' => 'published',
            'estado' => 1,
            'seo_title' => 'SEO title noticia',
            'meta_description' => 'Descripcion SEO noticia',
            'published_at' => now()->subDay(),
            'created_at' => now()->subDay(),
            'updated_at' => now(),
        ]);

        $response = $this->call('GET', '/noticias-del-folklore-argentino/titulo-visible-noticia', [], [], [], $this->serverVariables());

        $response->assertOk();
        $response->assertSee('<title>SEO title noticia</title>', false);
        $response->assertSee('<meta name="description" content="Descripcion SEO noticia">', false);
        $response->assertSee('<link rel="canonical" href="https://mifolkloreargentino.com/noticias-del-folklore-argentino/titulo-visible-noticia" />', false);
        $response->assertSee('<h1 class="text-2xl font-semibold text-gray-800 mb-2">Titulo visible noticia</h1>', false);
        $this->assertSame(1, substr_count($response->getContent(), '<h1'));
    }

    /** @test */
    public function event_and_festival_pages_generate_clean_descriptions_and_mark_helper_ctas_as_no_snippet(): void
    {
        $author = User::factory()->create();
        $provinciaId = DB::table('provincias')->insertGetId([
            'nombre' => 'Cordoba SEO Test',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('events')->insert([
            'id' => 3101,
            'title' => 'Festival en Cosquin',
            'slug' => 'festival-en-cosquin',
            'body' => '<p>Festival en Cosquin con artistas invitados y entrada libre.</p><p>Una cita clave del calendario.</p>',
            'province_id' => $provinciaId,
            'editorial_status' => 'published',
            'published_at' => now()->subDay(),
            'start_at' => now()->addWeek(),
            'created_by' => $author->id,
            'created_at' => now()->subDay(),
            'updated_at' => now(),
        ]);

        DB::table('festivales')->insert([
            'id' => 4101,
            'province_id' => $provinciaId,
            'mes_id' => 1,
            'title' => 'Fiesta de la Chaya',
            'slug' => 'fiesta-de-la-chaya',
            'body' => '<p>Celebracion popular con musica, comparsas y tradicion riojana.</p>',
            'visitas' => 0,
            'user_id' => $author->id,
            'status' => 'published',
            'published_at' => now()->subDay(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $event = $this->call('GET', '/cartelera-de-eventos-folkloricos/festival-en-cosquin', [], [], [], $this->serverVariables());
        $event->assertOk();
        $event->assertSee('content="Festival en Cosquin con artistas invitados y entrada libre. Una cita clave del calendario."', false);
        $event->assertSee('<h1 class="text-3xl font-bold mb-2">Festival en Cosquin</h1>', false);
        $event->assertSee('data-nosnippet', false);

        $festival = $this->call('GET', '/festivales-y-fiestas-tradicionales/fiesta-de-la-chaya', [], [], [], $this->serverVariables());
        $festival->assertOk();
        $festival->assertSee('content="Celebracion popular con musica, comparsas y tradicion riojana."', false);
        $festival->assertSee('<h1 class="text-3xl font-bold mb-2">Fiesta de la Chaya</h1>', false);
        $festival->assertSee('data-nosnippet', false);
    }

    /** @test */
    public function evergreen_album_and_lyrics_pages_have_non_empty_h1_and_clean_metadata(): void
    {
        $author = User::factory()->create();
        DB::table('interpretes')->insert([
            'id' => 5201,
            'interprete' => 'Mercedes Sosa SEO',
            'slug' => 'mercedes-sosa-seo',
            'biografia' => 'Cantante fundamental.',
            'estado' => 1,
            'user_id' => $author->id,
            'visitas' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('albunes')->insert([
            'id' => 5301,
            'interprete_id' => 5201,
            'album' => 'Mujeres Argentinas',
            'slug' => 'mujeres-argentinas-seo',
            'anio' => '1969',
            'visitas' => 0,
            'user_id' => $author->id,
            'estado' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('canciones')->insert([
            'id' => 5401,
            'interprete_id' => 5201,
            'cancion' => 'Alfonsina y el mar',
            'slug' => 'alfonsina-y-el-mar-seo',
            'letra' => "<p>Por la blanda arena que lame el mar<br>su pequeña huella no vuelve mas.</p>",
            'visitas' => 0,
            'user_id' => $author->id,
            'estado' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $category = KnowledgeCategory::factory()->create([
            'name' => 'Historia',
            'slug' => 'historia',
            'is_active' => true,
        ]);

        $article = KnowledgeArticle::factory()->published()->create([
            'knowledge_category_id' => $category->id,
            'title' => 'Origen de la zamba',
            'slug' => 'origen-de-la-zamba',
            'excerpt' => '<p>La zamba argentina se desarrollo con rasgos propios en distintas regiones.</p>',
            'body' => '<p>La zamba argentina se desarrollo con rasgos propios en distintas regiones y contextos historicos.</p>',
            'author_id' => $author->id,
            'seo_title' => null,
            'meta_description' => null,
        ]);

        $evergreen = $this->call('GET', '/enciclopedia/historia/origen-de-la-zamba', [], [], [], $this->serverVariables());
        $evergreen->assertOk();
        $evergreen->assertSee('<title>Origen de la zamba | Enciclopedia del folklore argentino</title>', false);
        $evergreen->assertSee('content="La zamba argentina se desarrollo con rasgos propios en distintas regiones."', false);
        $evergreen->assertSee('<h1 class="text-3xl md:text-4xl font-bold text-slate-900 mb-4">Origen de la zamba</h1>', false);
        $evergreen->assertSee('<link rel="canonical" href="https://mifolkloreargentino.com/enciclopedia/historia/origen-de-la-zamba" />', false);

        $album = $this->call('GET', '/mercedes-sosa-seo/discografia/mujeres-argentinas-seo', [], [], [], $this->serverVariables());
        $album->assertOk();
        $album->assertSee('<title>Mujeres Argentinas (1969) | Mercedes Sosa SEO</title>', false);
        $album->assertSee('<h1 class="text-2xl font-bold text-gray-800">Mujeres Argentinas</h1>', false);
        $album->assertDontSee('<h1 class="text-2xl font-bold text-gray-800"></h1>', false);

        $lyrics = $this->call('GET', '/mercedes-sosa-seo/letras/alfonsina-y-el-mar-seo', [], [], [], $this->serverVariables());
        $lyrics->assertOk();
        $lyrics->assertSee('<title>Letra de Alfonsina y el mar | Mercedes Sosa SEO</title>', false);
        $lyrics->assertSee('content="Por la blanda arena que lame el mar su pequeña huella no vuelve mas."', false);
        $lyrics->assertSee('<h1 class="text-2xl font-bold text-gray-900 mb-2">Alfonsina y el mar</h1>', false);
        $lyrics->assertSee('data-nosnippet', false);
    }

    private function serverVariables(): array
    {
        return [
            'HTTP_HOST' => 'mifolkloreargentino.com',
            'HTTPS' => 'on',
        ];
    }
}
