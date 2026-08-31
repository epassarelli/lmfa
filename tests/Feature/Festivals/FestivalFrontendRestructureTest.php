<?php

namespace Tests\Feature\Festivals;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class FestivalFrontendRestructureTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function province_month_landing_is_noindex_when_it_does_not_reach_the_configured_minimum(): void
    {
        $this->ensureMonth(1, 'Enero');
        $provinceId = DB::table('provincias')->insertGetId([
            'nombre' => 'Cordoba Festivales',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('festivales')->insert([
            [
                'province_id' => $provinceId,
                'mes_id' => 1,
                'title' => 'Festival Uno',
                'slug' => 'festival-uno',
                'body' => 'Detalle uno',
                'status' => 'published',
                'published_at' => now()->subDay(),
                'user_id' => 1,
                'visitas' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'province_id' => $provinceId,
                'mes_id' => 1,
                'title' => 'Festival Dos',
                'slug' => 'festival-dos',
                'body' => 'Detalle dos',
                'status' => 'published',
                'published_at' => now()->subDay(),
                'user_id' => 1,
                'visitas' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $response = $this->call('GET', '/festivales-y-fiestas-tradicionales/provincia/cordoba-festivales/mes/enero', [], [], [], $this->serverVariables());

        $response->assertOk();
        $response->assertSee('<meta name="robots" content="noindex,follow">', false);
        $response->assertSee('<link rel="canonical" href="https://mifolkloreargentino.com/festivales-y-fiestas-tradicionales/provincia/cordoba-festivales/mes/enero" />', false);
    }

    /** @test */
    public function festival_sitemap_includes_indexable_landings_and_excludes_non_indexable_province_month_combinations(): void
    {
        $this->ensureMonth(2, 'Febrero');
        $provinceA = DB::table('provincias')->insertGetId([
            'nombre' => 'Salta Sitemap',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $provinceB = DB::table('provincias')->insertGetId([
            'nombre' => 'Jujuy Sitemap',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        foreach ([
            [$provinceA, 2, 'festival-a'],
            [$provinceA, 2, 'festival-b'],
            [$provinceA, 2, 'festival-c'],
            [$provinceB, 2, 'festival-d'],
            [$provinceB, 2, 'festival-e'],
        ] as [$provinceId, $monthId, $slug]) {
            DB::table('festivales')->insert([
                'province_id' => $provinceId,
                'mes_id' => $monthId,
                'title' => str($slug)->headline()->toString(),
                'slug' => $slug,
                'body' => 'Detalle',
                'status' => 'published',
                'published_at' => now()->subDay(),
                'user_id' => 1,
                'visitas' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $response = $this->get('/sitemap-festivales.xml');

        $response->assertOk();
        $response->assertSee('https://mifolkloreargentino.com/festivales-y-fiestas-tradicionales/provincia/salta-sitemap', false);
        $response->assertSee('https://mifolkloreargentino.com/festivales-y-fiestas-tradicionales/mes/febrero', false);
        $response->assertSee('https://mifolkloreargentino.com/festivales-y-fiestas-tradicionales/provincia/salta-sitemap/mes/febrero', false);
        $response->assertDontSee('https://mifolkloreargentino.com/festivales-y-fiestas-tradicionales/provincia/jujuy-sitemap/mes/febrero', false);
    }

    /** @test */
    public function province_and_month_landings_resolve_using_generated_slugs(): void
    {
        $this->ensureMonth(1, 'Enero');
        $provinceId = DB::table('provincias')->insertGetId([
            'nombre' => 'Santiago del Estero Landing',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('festivales')->insert([
            'province_id' => $provinceId,
            'mes_id' => 1,
            'title' => 'Festival Landing',
            'slug' => 'festival-landing',
            'body' => 'Detalle landing',
            'status' => 'published',
            'published_at' => now()->subDay(),
            'user_id' => 1,
            'visitas' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $province = $this->call('GET', '/festivales-y-fiestas-tradicionales/provincia/santiago-del-estero-landing', [], [], [], $this->serverVariables());
        $province->assertOk();
        $province->assertSee('Festivales de folklore en Santiago del Estero Landing');

        $month = $this->call('GET', '/festivales-y-fiestas-tradicionales/mes/enero', [], [], [], $this->serverVariables());
        $month->assertOk();
        $month->assertSee('Festivales de folklore en Enero');

        $provinceMonth = $this->call('GET', '/festivales-y-fiestas-tradicionales/provincia/santiago-del-estero-landing/mes/enero', [], [], [], $this->serverVariables());
        $provinceMonth->assertOk();
        $provinceMonth->assertSee('Festivales de folklore en Santiago del Estero Landing durante Enero');
    }


    /** @test */
    public function festival_detail_uses_persisted_seo_metadata_and_effective_social_image(): void
    {
        $this->ensureMonth(1, 'Enero');
        $provinceId = DB::table('provincias')->insertGetId([
            'nombre' => 'Salta SEO Festival',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('festivales')->insert([
            'province_id' => $provinceId,
            'mes_id' => 1,
            'title' => 'Festival SEO',
            'slug' => 'festival-seo',
            'excerpt' => 'Bajada del festival.',
            'body' => '<p>Historia estable del festival.</p>',
            'seo_title' => 'Festival SEO personalizado',
            'meta_description' => 'Descripcion SEO personalizada del festival.',
            'image_alt' => 'Identidad visual del Festival SEO',
            'status' => 'published',
            'published_at' => now()->subDay(),
            'user_id' => 1,
            'visitas' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->call(
            'GET',
            '/festivales-y-fiestas-tradicionales/festival-seo',
            [],
            [],
            [],
            $this->serverVariables()
        );

        $response->assertOk();
        $response->assertSee('<title>Festival SEO personalizado | Folklore Argentino</title>', false);
        $response->assertSee('content="Descripcion SEO personalizada del festival."', false);
        $response->assertSee('/img/fallbacks/festival-default.webp', false);
        $response->assertSee('Identidad visual del Festival SEO', false);
    }

    private function ensureMonth(int $id, string $name): void
    {
        DB::table('meses')->updateOrInsert(
            ['id' => $id],
            [
                'nombre' => $name,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }

    private function serverVariables(): array
    {
        return [
            'HTTP_HOST' => 'mifolkloreargentino.com',
            'HTTPS' => 'on',
        ];
    }
}
