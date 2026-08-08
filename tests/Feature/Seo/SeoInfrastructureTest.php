<?php

namespace Tests\Feature\Seo;

use App\Models\Categoria;
use App\Models\KnowledgeArticle;
use App\Models\KnowledgeCategory;
use App\Models\News;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SeoInfrastructureTest extends TestCase
{
    use DatabaseTransactions;

    private const ACTIVE_SITEMAPS = [
        'https://mifolkloreargentino.com/sitemap-estaticas.xml',
        'https://mifolkloreargentino.com/sitemap-artistas.xml',
        'https://mifolkloreargentino.com/sitemap-biografias.xml',
        'https://mifolkloreargentino.com/sitemap-noticias.xml',
        'https://mifolkloreargentino.com/sitemap-google-news.xml',
        'https://mifolkloreargentino.com/sitemap-eventos.xml',
        'https://mifolkloreargentino.com/sitemap-festivales.xml',
        'https://mifolkloreargentino.com/sitemap-discografias.xml',
        'https://mifolkloreargentino.com/sitemap-letras.xml',
        'https://mifolkloreargentino.com/sitemap-evergreen.xml',
    ];

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    /** @test */
    public function it_redirects_http_non_www_to_the_https_canonical_domain_without_losing_query_string(): void
    {
        $response = $this->call('GET', 'http://mifolkloreargentino.com/contacto?foo=bar&utm_source=ads');

        $response->assertRedirect('https://mifolkloreargentino.com/contacto?foo=bar&utm_source=ads');
        $this->assertSame(301, $response->getStatusCode());
    }

    /** @test */
    public function it_redirects_http_www_to_the_https_canonical_domain_without_losing_query_string(): void
    {
        $response = $this->call('GET', 'http://www.mifolkloreargentino.com/contacto?foo=bar&utm_source=ads');

        $response->assertRedirect('https://mifolkloreargentino.com/contacto?foo=bar&utm_source=ads');
        $this->assertSame(301, $response->getStatusCode());
    }

    /** @test */
    public function it_redirects_https_www_to_the_https_canonical_domain_without_losing_query_string(): void
    {
        $response = $this->call('GET', 'https://www.mifolkloreargentino.com/contacto?foo=bar&utm_source=ads');

        $response->assertRedirect('https://mifolkloreargentino.com/contacto?foo=bar&utm_source=ads');
        $this->assertSame(301, $response->getStatusCode());
    }

    /** @test */
    public function it_redirects_https_com_ar_www_to_the_https_canonical_domain_without_losing_query_string(): void
    {
        $response = $this->call('GET', 'https://www.mifolkloreargentino.com.ar/contacto?foo=bar&utm_source=ads');

        $response->assertRedirect('https://mifolkloreargentino.com/contacto?foo=bar&utm_source=ads');
        $this->assertSame(301, $response->getStatusCode());
    }

    /** @test */
    public function it_serves_the_page_normally_when_request_already_uses_the_canonical_host(): void
    {
        $response = $this->call('GET', '/contacto', [], [], [], [
            'HTTP_HOST' => 'mifolkloreargentino.com',
            'HTTPS' => 'on',
        ]);

        $response->assertOk();
    }

    /** @test */
    public function apex_https_root_responds_200_without_redirecting_to_itself(): void
    {
        $response = $this->call('GET', '/', [], [], [], [
            'HTTP_HOST' => 'mifolkloreargentino.com',
            'HTTPS' => 'on',
        ]);

        $response->assertOk();
    }

    /** @test */
    public function apex_https_root_with_query_responds_200_without_redirecting(): void
    {
        $response = $this->call('GET', '/?foo=bar', [], [], [], [
            'HTTP_HOST' => 'mifolkloreargentino.com',
            'HTTPS' => 'on',
        ]);

        $response->assertOk();
        $response->assertSee('href="https://mifolkloreargentino.com/?foo=bar"', false);
    }

    /** @test */
    public function https_www_root_redirects_once_to_apex_root(): void
    {
        $response = $this->call('GET', 'https://www.mifolkloreargentino.com/');

        $response->assertRedirect('https://mifolkloreargentino.com/');
        $this->assertSame(301, $response->getStatusCode());
    }

    /** @test */
    public function https_www_root_with_query_redirects_once_to_apex_root_preserving_query(): void
    {
        $response = $this->call('GET', 'https://www.mifolkloreargentino.com/?foo=bar');

        $response->assertRedirect('https://mifolkloreargentino.com/?foo=bar');
        $this->assertSame(301, $response->getStatusCode());
    }

    /** @test */
    public function equivalent_internal_routes_keep_200_on_apex_and_301_on_www(): void
    {
        $apex = $this->call('GET', '/contacto?foo=bar', [], [], [], [
            'HTTP_HOST' => 'mifolkloreargentino.com',
            'HTTPS' => 'on',
        ]);

        $apex->assertOk();
        $apex->assertSee('href="https://mifolkloreargentino.com/contacto?foo=bar"', false);

        $www = $this->call('GET', 'https://www.mifolkloreargentino.com/contacto?foo=bar');

        $www->assertRedirect('https://mifolkloreargentino.com/contacto?foo=bar');
        $this->assertSame(301, $www->getStatusCode());
    }

    /** @test */
    public function it_does_not_redirect_when_proxy_headers_already_indicate_the_canonical_https_request(): void
    {
        $response = $this->call('GET', '/contacto?foo=bar', [], [], [], [
            'HTTP_HOST' => 'internal-origin',
            'HTTPS' => 'off',
            'HTTP_X_FORWARDED_HOST' => 'mifolkloreargentino.com',
            'HTTP_X_FORWARDED_PROTO' => 'https',
            'HTTP_X_FORWARDED_PORT' => '443',
        ]);

        $response->assertOk();
        $response->assertSee('href="https://mifolkloreargentino.com/contacto?foo=bar"', false);
    }

    /** @test */
    public function it_renders_a_single_canonical_tag_and_strips_tracking_parameters_from_it(): void
    {
        $response = $this->call('GET', '/contacto?page=2&utm_source=ads&foo=bar', [], [], [], [
            'HTTP_HOST' => 'mifolkloreargentino.com',
            'HTTPS' => 'on',
        ]);

        $response->assertOk();
        $response->assertSee('href="https://mifolkloreargentino.com/contacto?', false);
        $response->assertSee('page=2', false);
        $response->assertSee('foo=bar', false);
        $response->assertDontSee('utm_source', false);
        $this->assertSame(1, substr_count($response->getContent(), '<link rel="canonical"'));
    }

    /** @test */
    public function sitemap_index_is_valid_xml_and_lists_the_active_sitemaps(): void
    {
        $response = $this->xmlRequest('/sitemap.xml');

        $locs = $this->extractLocs($response->getContent());

        $this->assertSame(self::ACTIVE_SITEMAPS, $locs);
        $response->assertDontSee('.com.ar', false);
    }

    /** @test */
    public function each_active_sitemap_responds_with_valid_xml_and_legacy_endpoints_redirect(): void
    {
        foreach (self::ACTIVE_SITEMAPS as $url) {
            $path = parse_url($url, PHP_URL_PATH);
            $response = $this->xmlRequest($path);
            $response->assertOk();
        }

        $this->call('GET', '/sitemap-main.xml', [], [], [], $this->serverVariables())
            ->assertRedirect('https://mifolkloreargentino.com/sitemap.xml');

        $this->call('GET', '/sitemap-news.xml', [], [], [], $this->serverVariables())
            ->assertRedirect('https://mifolkloreargentino.com/sitemap-google-news.xml');
    }

    /** @test */
    public function sitemap_noticias_includes_published_old_news_and_tolerates_null_created_at(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-08-06 12:00:00'));

        $categoriaId = $this->createCategoria('general-noticias');

        DB::table('news')->insert([
            [
                'title' => 'Noticia vieja publicada',
                'slug' => 'noticia-vieja-publicada',
                'body' => 'Contenido',
                'categoria_id' => $categoriaId,
                'editorial_status' => 'published',
                'estado' => 1,
                'published_at' => Carbon::now()->subDays(30),
                'created_at' => null,
                'updated_at' => Carbon::now()->subDays(1),
            ],
            [
                'title' => 'Noticia futura',
                'slug' => 'noticia-futura',
                'body' => 'Contenido',
                'categoria_id' => $categoriaId,
                'editorial_status' => 'published',
                'estado' => 1,
                'published_at' => Carbon::now()->addDay(),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'title' => 'Noticia draft',
                'slug' => 'noticia-draft',
                'body' => 'Contenido',
                'categoria_id' => $categoriaId,
                'editorial_status' => 'draft',
                'estado' => 0,
                'published_at' => null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ]);

        $response = $this->xmlRequest('/sitemap-noticias.xml');
        $locs = $this->extractLocs($response->getContent());

        $this->assertContains('https://mifolkloreargentino.com/noticias-del-folklore-argentino/noticia-vieja-publicada', $locs);
        $this->assertNotContains('https://mifolkloreargentino.com/noticias-del-folklore-argentino/noticia-futura', $locs);
        $this->assertNotContains('https://mifolkloreargentino.com/noticias-del-folklore-argentino/noticia-draft', $locs);
        $this->assertStringContainsString('<lastmod>2026-08-05T12:00:00+00:00</lastmod>', $response->getContent());
    }

    /** @test */
    public function sitemap_google_news_uses_published_at_and_applies_the_two_day_window(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-08-06 12:00:00'));

        $categoriaId = $this->createCategoria('general-google-news');

        DB::table('news')->insert([
            [
                'title' => 'Noticia reciente',
                'slug' => 'noticia-reciente',
                'body' => 'Contenido',
                'categoria_id' => $categoriaId,
                'editorial_status' => 'published',
                'estado' => 1,
                'published_at' => Carbon::now()->subHours(6),
                'created_at' => null,
                'updated_at' => Carbon::now()->subHours(3),
            ],
            [
                'title' => 'Noticia sin published at',
                'slug' => 'noticia-sin-published-at',
                'body' => 'Contenido',
                'categoria_id' => $categoriaId,
                'editorial_status' => 'published',
                'estado' => 1,
                'published_at' => null,
                'created_at' => null,
                'updated_at' => Carbon::now()->subHours(2),
            ],
            [
                'title' => 'Noticia vieja',
                'slug' => 'noticia-vieja-google-news',
                'body' => 'Contenido',
                'categoria_id' => $categoriaId,
                'editorial_status' => 'published',
                'estado' => 1,
                'published_at' => Carbon::now()->subDays(3),
                'created_at' => Carbon::now()->subDays(3),
                'updated_at' => Carbon::now()->subDays(3),
            ],
            [
                'title' => 'Noticia futura google news',
                'slug' => 'noticia-futura-google-news',
                'body' => 'Contenido',
                'categoria_id' => $categoriaId,
                'editorial_status' => 'published',
                'estado' => 1,
                'published_at' => Carbon::now()->addHour(),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'title' => 'Noticia draft google news',
                'slug' => 'noticia-draft-google-news',
                'body' => 'Contenido',
                'categoria_id' => $categoriaId,
                'editorial_status' => 'draft',
                'estado' => 0,
                'published_at' => Carbon::now()->subHour(),
                'created_at' => Carbon::now()->subHour(),
                'updated_at' => Carbon::now()->subHour(),
            ],
        ]);

        $response = $this->xmlRequest('/sitemap-google-news.xml');
        $locs = $this->extractLocs($response->getContent());

        $this->assertSame([
            'https://mifolkloreargentino.com/noticias-del-folklore-argentino/noticia-reciente',
        ], $locs);
        $this->assertStringContainsString('<news:publication_date>2026-08-06T06:00:00+00:00</news:publication_date>', $response->getContent());
        $this->assertStringNotContainsString('noticia-sin-published-at', $response->getContent());
    }

    /** @test */
    public function google_news_empty_is_still_valid_xml(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-08-06 12:00:00'));

        $response = $this->xmlRequest('/sitemap-google-news.xml');

        $this->assertSame([], $this->extractLocs($response->getContent()));
    }

    /** @test */
    public function artists_and_biographies_appear_in_their_corresponding_sitemaps(): void
    {
        DB::table('interpretes')->insert([
            'interprete' => 'Artista sitemap',
            'slug' => 'artista-sitemap',
            'biografia' => 'Biografia completa del artista sitemap con suficiente contenido.',
            'estado' => 1,
            'created_at' => Carbon::parse('2026-01-01 00:00:00'),
            'updated_at' => Carbon::parse('2026-08-01 00:00:00'),
        ]);

        $artists = $this->extractLocs($this->xmlRequest('/sitemap-artistas.xml')->getContent());
        $biographies = $this->extractLocs($this->xmlRequest('/sitemap-biografias.xml')->getContent());

        $this->assertContains('https://mifolkloreargentino.com/artista-sitemap', $artists);
        $this->assertContains('https://mifolkloreargentino.com/artista-sitemap/biografia', $biographies);
    }

    /** @test */
    public function all_general_sitemaps_exclude_storage_files_admin_urls_and_cross_sitemap_duplicates(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-08-06 12:00:00'));

        $categoriaId = $this->createCategoria('general-duplicates');
        DB::table('interpretes')->insert([
            'id' => 9001,
            'interprete' => 'Artista unico',
            'slug' => 'artista-unico',
            'biografia' => 'Hub y biografia separados.',
            'estado' => 1,
            'created_at' => Carbon::now()->subMonth(),
            'updated_at' => Carbon::now()->subDay(),
        ]);

        DB::table('canciones')->insert([
            'cancion' => 'Cancion unica',
            'slug' => 'cancion-unica',
            'letra' => 'Letra unica',
            'visitas' => 0,
            'estado' => 1,
            'interprete_id' => 9001,
            'created_at' => Carbon::now()->subMonth(),
            'updated_at' => Carbon::now()->subDay(),
        ]);

        DB::table('albunes')->insert([
            'album' => 'Album unico',
            'slug' => 'album-unico',
            'anio' => 2020,
            'visitas' => 0,
            'estado' => 1,
            'interprete_id' => 9001,
            'created_at' => Carbon::now()->subMonth(),
            'updated_at' => Carbon::now()->subDay(),
        ]);

        $provinciaId = DB::table('provincias')->insertGetId([
            'nombre' => 'Provincia sitemap',
            'created_at' => Carbon::now()->subMonth(),
            'updated_at' => Carbon::now()->subDay(),
        ]);

        DB::table('festivales')->insert([
            'province_id' => $provinciaId,
            'mes_id' => 1,
            'title' => 'Festival unico',
            'slug' => 'festival-unico',
            'body' => 'Detalle festival',
            'visitas' => 0,
            'user_id' => 1,
            'status' => 'published',
            'published_at' => Carbon::now()->subMonth(),
            'created_at' => Carbon::now()->subMonth(),
            'updated_at' => Carbon::now()->subDay(),
        ]);

        DB::table('mitos')->insert([
            'titulo' => 'Mito unico',
            'slug' => 'mito-unico',
            'mito' => 'Texto mito',
            'foto' => 'mito-unico.jpg',
            'visitas' => 0,
            'estado' => 1,
            'created_at' => Carbon::now()->subMonth(),
            'updated_at' => Carbon::now()->subDay(),
        ]);

        DB::table('comidas')->insert([
            'titulo' => 'Comida unica',
            'slug' => 'comida-unica',
            'receta' => 'Texto receta',
            'foto' => 'comida-unica.jpg',
            'visitas' => 0,
            'estado' => 1,
            'created_at' => Carbon::now()->subMonth(),
            'updated_at' => Carbon::now()->subDay(),
        ]);

        DB::table('news')->insert([
            'title' => 'Noticia unica sitemap',
            'slug' => 'noticia-unica-sitemap',
            'body' => 'Contenido',
            'categoria_id' => $categoriaId,
            'editorial_status' => 'published',
            'estado' => 1,
            'published_at' => Carbon::now()->subDays(10),
            'created_at' => Carbon::now()->subDays(10),
            'updated_at' => Carbon::now()->subDays(1),
        ]);

        $author = User::factory()->create();
        $knowledgeCategory = KnowledgeCategory::factory()->create(['slug' => 'historia-sitemap']);
        KnowledgeArticle::factory()->published()->create([
            'knowledge_category_id' => $knowledgeCategory->id,
            'title' => 'Articulo unico sitemap',
            'slug' => 'articulo-unico-sitemap',
            'author_id' => $author->id,
            'published_at' => Carbon::now()->subDays(5),
        ]);

        $generalPaths = [
            '/sitemap-estaticas.xml',
            '/sitemap-artistas.xml',
            '/sitemap-biografias.xml',
            '/sitemap-noticias.xml',
            '/sitemap-eventos.xml',
            '/sitemap-festivales.xml',
            '/sitemap-discografias.xml',
            '/sitemap-letras.xml',
            '/sitemap-evergreen.xml',
        ];

        $allLocs = [];

        foreach ($generalPaths as $path) {
            $content = $this->xmlRequest($path)->getContent();
            $locs = $this->extractLocs($content);

            foreach ($locs as $loc) {
                $this->assertStringNotContainsString('/storage/', $loc);
                $this->assertStringNotContainsString('/public/storage/', $loc);
                $this->assertStringNotContainsString('/admin', $loc);
                $this->assertStringNotContainsString('www.', $loc);
                $this->assertStringNotContainsString('.com.ar', $loc);
                $this->assertMatchesRegularExpression('#^https://mifolkloreargentino\.com/#', $loc);
                $this->assertDoesNotMatchRegularExpression('/\.(jpg|jpeg|png|webp|gif|svg|pdf)$/i', $loc);
            }

            $allLocs = array_merge($allLocs, $locs);
        }

        $duplicates = array_diff_assoc($allLocs, array_unique($allLocs));

        $this->assertSame([], array_values($duplicates));
    }

    /** @test */
    public function published_news_page_renders_even_when_legacy_news_lacks_created_at(): void
    {
        $categoriaId = $this->createCategoria('general-legacy');

        $newsId = DB::table('news')->insertGetId([
            'title' => 'Noticia legacy',
            'slug' => 'noticia-legacy',
            'body' => 'Contenido legacy',
            'categoria_id' => $categoriaId,
            'editorial_status' => 'published',
            'estado' => 1,
            'published_at' => null,
            'created_at' => null,
            'updated_at' => now(),
        ]);

        $response = $this->call('GET', '/noticias-del-folklore-argentino/noticia-legacy', [], [], [], $this->serverVariables());

        $response->assertOk();
        $response->assertSee('Noticia legacy');
        $this->assertSame(1, DB::table('news')->where('id', $newsId)->value('visitas'));
    }

    /** @test */
    public function news_detail_returns_404_for_drafts(): void
    {
        $categoriaId = $this->createCategoria('general-draft');

        News::create([
            'title' => 'Noticia borrador privada',
            'slug' => 'noticia-borrador-privada',
            'body' => 'Contenido borrador',
            'categoria_id' => $categoriaId,
            'editorial_status' => 'draft',
            'estado' => 0,
        ]);

        $response = $this->call('GET', '/noticias-del-folklore-argentino/noticia-borrador-privada', [], [], [], $this->serverVariables());

        $response->assertNotFound();
    }

    /** @test */
    public function robots_file_points_only_to_the_canonical_sitemap(): void
    {
        $robots = file_get_contents(public_path('robots.txt'));

        $this->assertStringContainsString('Sitemap: https://mifolkloreargentino.com/sitemap.xml', $robots);
        $this->assertStringNotContainsString('sitemap-estaticas.xml', $robots);
        $this->assertStringNotContainsString('.com.ar', $robots);
    }

    private function xmlRequest(string $path)
    {
        $response = $this->call('GET', $path, [], [], [], $this->serverVariables());

        $response->assertOk();
        $this->assertStringContainsString('application/xml', $response->headers->get('Content-Type'));
        $this->assertXmlString($response->getContent());

        return $response;
    }

    private function extractLocs(string $xml): array
    {
        $document = simplexml_load_string($xml);
        $locs = [];

        if (! $document) {
            return $locs;
        }

        foreach ($document->children('http://www.sitemaps.org/schemas/sitemap/0.9') as $node) {
            if (isset($node->loc)) {
                $locs[] = (string) $node->loc;
            }
        }

        return $locs;
    }

    private function assertXmlString(string $xml): void
    {
        libxml_use_internal_errors(true);
        $document = simplexml_load_string($xml);
        $errors = libxml_get_errors();
        libxml_clear_errors();

        $this->assertNotFalse($document, 'XML invalido: '.json_encode($errors));
    }

    private function createCategoria(string $slug): int
    {
        return DB::table('categorias')->insertGetId([
            'nombre' => 'General',
            'slug' => $slug,
            'metetittle' => 'General',
            'metadescription' => 'Categoria general',
        ]);
    }

    private function serverVariables(): array
    {
        return [
            'HTTP_HOST' => 'mifolkloreargentino.com',
            'HTTPS' => 'on',
        ];
    }
}
