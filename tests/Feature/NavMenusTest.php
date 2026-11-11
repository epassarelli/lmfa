<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class NavMenusTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        if (! getenv('CI')) {
            $this->assertSame('mfa_test', DB::connection()->getDatabaseName());
        }
    }

    public function test_discos_y_canciones_van_en_el_nav_principal_detras_de_festivales(): void
    {
        $html = $this->get('/')->assertOk()->getContent();

        $festivales = strpos($html, '>Festivales</a>');
        $discos = strpos($html, '>Discos</a>');
        $canciones = strpos($html, '>Canciones</a>');
        $mas = strpos($html, 'Más');

        $this->assertNotFalse($festivales);
        $this->assertGreaterThan($festivales, $discos);
        $this->assertGreaterThan($discos, $canciones);
        $this->assertLessThan($mas, $canciones, 'Discos y Canciones deben quedar antes del desplegable "Más"');
        $this->assertSame(1, substr_count($html, '>Discos</a>'), 'Discos no debe repetirse dentro de "Más"');
        $this->assertSame(1, substr_count($html, '>Canciones</a>'), 'Canciones no debe repetirse dentro de "Más"');
    }

    public function test_backoffice_centro_de_moderacion_abre_el_menu_y_publicaciones_en_redes_pasa_a_su_lugar(): void
    {
        $menu = array_values(array_filter(config('adminlte.menu'), fn ($i) => isset($i['text']) || isset($i['header'])));

        $this->assertSame('Centro de Moderación', $menu[0]['text']);

        $texts = array_map(fn ($i) => $i['text'] ?? '#'.$i['header'], $menu);
        $this->assertContains('Publicaciones en redes', $texts);
        $this->assertGreaterThan(
            array_search('#ASTRONOMÍA Y AUDIENCIA', $texts, true),
            array_search('Publicaciones en redes', $texts, true)
        );
        $this->assertLessThan(
            array_search('#SISTEMA Y AUDIENCIA', $texts, true),
            array_search('Publicaciones en redes', $texts, true)
        );
        $this->assertSame(1, count(array_keys($texts, 'Centro de Moderación', true)));
    }
}
