<?php

namespace Tests\Feature\Myths;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class MythFrontendModernizationTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function myth_detail_uses_persisted_seo_and_grounded_article_schema(): void
    {
        $user = User::factory()->create();

        DB::table('mitos')->insert([
            'titulo' => 'Leyenda Frontend',
            'content_type' => 'legend',
            'slug' => 'leyenda-frontend',
            'mito' => '<p>Relato tradicional de prueba.</p>',
            'excerpt' => 'Bajada editorial.',
            'region' => 'Patagonia',
            'seo_title' => 'Leyenda Frontend: relato patagónico',
            'meta_description' => 'Meta description persistida para la leyenda.',
            'foto' => null,
            'publicar' => now(),
            'user_id' => $user->id,
            'visitas' => 0,
            'estado' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->get('/mitos-y-leyendas-argentinas/leyenda-frontend');

        $response->assertOk();
        $response->assertSee('Leyenda Frontend: relato patagónico', false);
        $response->assertSee('Meta description persistida para la leyenda.', false);
        $response->assertSee('"@type": "Article"', false);
    }

    /** @test */
    public function inactive_myth_is_not_publicly_accessible(): void
    {
        $user = User::factory()->create();

        DB::table('mitos')->insert([
            'titulo' => 'Mito inactivo',
            'slug' => 'mito-inactivo',
            'mito' => '<p>Relato pendiente de revisión.</p>',
            'foto' => null,
            'publicar' => null,
            'user_id' => $user->id,
            'visitas' => 0,
            'estado' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->get('/mitos-y-leyendas-argentinas/mito-inactivo')->assertNotFound();
    }
}
