<?php

namespace Tests\Feature\Seo;

use App\Models\Categoria;
use App\Models\KnowledgeArticle;
use App\Models\KnowledgeCategory;
use App\Models\News;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SeoInfrastructureTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function it_redirects_non_canonical_hosts_to_the_https_dot_com_domain_without_losing_query_string(): void
    {
        $response = $this->get('https://www.mifolkloreargentino.com.ar/contacto?foo=bar&utm_source=ads');

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
    public function sitemap_index_is_valid_xml_and_points_only_to_com_sitemaps(): void
    {
        $response = $this->call('GET', '/sitemap.xml', [], [], [], [
            'HTTP_HOST' => 'mifolkloreargentino.com',
            'HTTPS' => 'on',
        ]);

        $response->assertOk();
        $this->assertStringContainsString('application/xml', $response->headers->get('Content-Type'));
        $response->assertSee('<sitemapindex', false);
        $response->assertSee('https://mifolkloreargentino.com/sitemap-main.xml', false);
        $response->assertSee('https://mifolkloreargentino.com/sitemap-news.xml', false);
        $response->assertDontSee('.com.ar', false);
    }

    /** @test */
    public function main_sitemap_includes_public_content_and_excludes_drafts(): void
    {
        $categoriaId = DB::table('categorias')->insertGetId([
            'nombre' => 'General',
            'slug' => 'general',
            'metetittle' => 'General',
            'metadescription' => 'Categoria general',
        ]);
        $author = User::factory()->create();
        $knowledgeCategory = KnowledgeCategory::factory()->create(['slug' => 'historia']);

        News::create([
            'title' => 'Noticia publicada',
            'slug' => 'noticia-publicada',
            'body' => 'Contenido publicado',
            'featured_image_path' => 'news/noticia-publicada.jpg',
            'categoria_id' => $categoriaId,
            'editorial_status' => 'published',
            'published_at' => now()->subHour(),
            'estado' => 1,
        ]);

        News::create([
            'title' => 'Noticia borrador',
            'slug' => 'noticia-borrador',
            'body' => 'Contenido borrador',
            'featured_image_path' => 'news/noticia-borrador.jpg',
            'categoria_id' => $categoriaId,
            'editorial_status' => 'draft',
            'published_at' => null,
            'estado' => 0,
        ]);

        KnowledgeArticle::factory()->published()->create([
            'knowledge_category_id' => $knowledgeCategory->id,
            'title' => 'Articulo publicado',
            'slug' => 'articulo-publicado',
            'author_id' => $author->id,
        ]);

        KnowledgeArticle::factory()->create([
            'knowledge_category_id' => $knowledgeCategory->id,
            'title' => 'Articulo borrador',
            'slug' => 'articulo-borrador',
            'author_id' => $author->id,
            'editorial_status' => 'draft',
            'published_at' => null,
        ]);

        $response = $this->call('GET', '/sitemap-main.xml', [], [], [], [
            'HTTP_HOST' => 'mifolkloreargentino.com',
            'HTTPS' => 'on',
        ]);

        $response->assertOk();
        $response->assertSee('https://mifolkloreargentino.com/noticias-del-folklore-argentino/noticia-publicada', false);
        $response->assertSee('https://mifolkloreargentino.com/enciclopedia/historia/articulo-publicado', false);
        $response->assertDontSee('noticia-borrador', false);
        $response->assertDontSee('articulo-borrador', false);
        $response->assertDontSee('.com.ar', false);
    }

    /** @test */
    public function news_sitemap_returns_valid_xml_even_when_no_news_is_eligible(): void
    {
        $response = $this->call('GET', '/sitemap-news.xml', [], [], [], [
            'HTTP_HOST' => 'mifolkloreargentino.com',
            'HTTPS' => 'on',
        ]);

        $response->assertOk();
        $this->assertStringContainsString('application/xml', $response->headers->get('Content-Type'));
        $response->assertSee('<urlset', false);
    }

    /** @test */
    public function robots_file_points_only_to_the_canonical_sitemap(): void
    {
        $robots = file_get_contents(public_path('robots.txt'));

        $this->assertStringContainsString('Sitemap: https://mifolkloreargentino.com/sitemap.xml', $robots);
        $this->assertStringNotContainsString('.com.ar', $robots);
    }
}
