<?php

namespace Tests\Feature\Artists;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ArtistBiographyFrontendTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function soloist_biography_uses_person_schema_persisted_seo_and_no_fake_faq_schema(): void
    {
        DB::table('interpretes')->insert([
            'interprete' => 'Solista Schema',
            'artist_type' => 'soloist',
            'slug' => 'solista-schema',
            'biografia' => '<p>Biografía pública de prueba con trayectoria artística documentada.</p>',
            'excerpt' => 'Resumen editorial.',
            'seo_title' => 'Solista Schema: biografía oficial',
            'meta_description' => 'Meta description persistida para la biografía.',
            'image_alt' => 'Retrato de Solista Schema',
            'user_id' => 1,
            'visitas' => 0,
            'estado' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->get('/solista-schema/biografia');

        $response->assertOk();
        $response->assertSee('Solista Schema: biografía oficial', false);
        $response->assertSee('Meta description persistida para la biografía.', false);
        $response->assertSee('"@type": "Person"', false);
        $response->assertDontSee('"@type": "FAQPage"', false);
    }
}
