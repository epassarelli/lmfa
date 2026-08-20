<?php

namespace Tests\Feature\Mitos;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class MitosFrontendTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function myths_home_renders_published_collections_successfully(): void
    {
        Cache::flush();

        DB::table('mitos')->insert([
            [
                'id' => 81001,
                'titulo' => 'Alma mula',
                'slug' => 'alma-mula',
                'mito' => '<p>Mito publicado.</p>',
                'foto' => 'alma-mula.jpg',
                'visitas' => 40,
                'estado' => 1,
                'created_at' => now()->subDays(2),
                'updated_at' => now()->subDay(),
            ],
            [
                'id' => 81002,
                'titulo' => 'Luz mala',
                'slug' => 'luz-mala',
                'mito' => '<p>Otro mito publicado.</p>',
                'foto' => 'luz-mala.jpg',
                'visitas' => 15,
                'estado' => 1,
                'created_at' => now()->subDay(),
                'updated_at' => now(),
            ],
            [
                'id' => 81003,
                'titulo' => 'Mito oculto',
                'slug' => 'mito-oculto',
                'mito' => '<p>No deberia verse.</p>',
                'foto' => 'mito-oculto.jpg',
                'visitas' => 99,
                'estado' => 0,
                'created_at' => now()->subDay(),
                'updated_at' => now(),
            ],
        ]);

        $response = $this->call('GET', '/mitos-y-leyendas-argentinas', [], [], [], $this->serverVariables());

        $response->assertOk();
        $response->assertSee('Mitos y leyendas argentinas');
        $response->assertSee('Alma mula');
        $response->assertSee('Luz mala');
        $response->assertDontSee('Mito oculto');
    }

    private function serverVariables(): array
    {
        return [
            'HTTP_HOST' => 'mifolkloreargentino.com',
            'HTTPS' => 'on',
        ];
    }
}
