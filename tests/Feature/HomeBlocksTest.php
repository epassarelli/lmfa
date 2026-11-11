<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class HomeBlocksTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        if (! getenv('CI')) {
            $this->assertSame('mfa_test', DB::connection()->getDatabaseName());
        }
        Cache::forget('home:ultimas-noticias');
        Cache::forget('home:bloques');
    }

    public function test_home_enlaza_a_todas_las_noticias(): void
    {
        $this->get('/')->assertOk()
            ->assertSee('Ver todas las noticias')
            ->assertSee(route('noticias.index'), false);
    }

    public function test_home_ya_no_muestra_el_bloque_de_destacados_unicos(): void
    {
        $this->get('/')->assertOk()->assertDontSee('Destacados del catálogo');
    }

    public function test_cada_bloque_muestra_como_maximo_cuatro_items_y_enlaza_a_su_seccion(): void
    {
        $html = $this->get('/')->assertOk()->getContent();

        foreach (['eventos' => route('cartelera.index'), 'interpretes' => route('interpretes.index'),
                  'festivales' => route('festivales.index'), 'discos' => route('discografias.index')] as $key => $url) {
            if (! str_contains($html, 'data-home-block="'.$key.'"')) {
                continue; // sin datos en la base de test, el bloque se omite
            }
            preg_match('/data-home-block="'.$key.'".*?<\/section>/s', $html, $m);
            $this->assertLessThanOrEqual(4, substr_count($m[0], '<a href=') - 1, "bloque {$key}");
            $this->assertStringContainsString($url, $m[0]);
        }
    }
}
