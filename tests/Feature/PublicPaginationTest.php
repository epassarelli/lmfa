<?php

namespace Tests\Feature;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class PublicPaginationTest extends TestCase
{
    // Paginadores en memoria: estas pruebas no consultan ni modifican la BD.
    private function renderPagination($paginator): string
    {
        return Blade::render('<x-public-pagination :paginator="$paginator" />', compact('paginator'));
    }

    public function test_numbered_pagination_is_spanish_even_with_english_locale(): void
    {
        app()->setLocale('en');
        $html = $this->renderPagination(new LengthAwarePaginator(range(1, 12), 1200, 12, 50, ['path' => '/festivales']));

        foreach (['Paginación', 'Anterior', 'Siguiente', 'Mostrando 589–600 de 1200 resultados', 'Ir a la página 49', 'aria-current="page"', '…'] as $text) {
            $this->assertStringContainsString($text, $html);
        }
        foreach (['Previous', 'Next', 'Showing', 'results', 'Go to page', 'page-link', '<svg'] as $text) {
            $this->assertStringNotContainsString($text, $html);
        }
        $this->assertLessThan(15, substr_count($html, '<a '));
    }

    public function test_first_and_last_pages_have_disabled_controls_without_dead_links(): void
    {
        $first = $this->renderPagination(new LengthAwarePaginator(range(1, 12), 24, 12, 1, ['path' => '/radios']));
        $last = $this->renderPagination(new LengthAwarePaginator(range(1, 12), 24, 12, 2, ['path' => '/radios']));

        $this->assertStringNotContainsString('rel="prev"', $first);
        $this->assertStringContainsString('rel="next"', $first);
        $this->assertStringNotContainsString('rel="next"', $last);
        $this->assertStringContainsString('rel="prev"', $last);
        foreach ([$first, $last] as $html) {
            $this->assertStringContainsString('aria-disabled="true"', $html);
            $this->assertStringNotContainsString('href="#"', $html);
        }
    }

    public function test_simple_pagination_does_not_require_or_invent_a_total(): void
    {
        $paginator = new Paginator(range(1, 13), 12, 2, ['path' => '/noticias']);
        $html = $this->renderPagination($paginator);

        $this->assertStringContainsString('Página', $html);
        $this->assertStringContainsString('>2</span>', $html);
        $this->assertStringContainsString('rel="prev"', $html);
        $this->assertStringContainsString('rel="next"', $html);
        $this->assertStringNotContainsString('resultados', $html);
        $this->assertStringNotContainsString('Mostrando', $html);
        $this->assertStringNotContainsString('Ir a la página 3', $html);
    }

    public function test_filters_custom_page_parameter_and_fragment_survive_navigation(): void
    {
        Paginator::queryStringResolver(fn () => ['provincia' => 'cordoba', 'q' => 'peña', 'pagina' => 2]);
        $paginator = new LengthAwarePaginator(range(1, 12), 48, 12, 2, ['path' => '/penias', 'pageName' => 'pagina']);
        $paginator->fragment('listado');
        $html = html_entity_decode($this->renderPagination($paginator), ENT_QUOTES, 'UTF-8');

        $this->assertStringContainsString('href="/penias?provincia=cordoba&q=pe%C3%B1a&pagina=3#listado"', $html);
        $this->assertStringContainsString('href="/penias?provincia=cordoba&q=pe%C3%B1a&pagina=1#listado"', $html);
        $this->assertStringNotContainsString('&page=', $html);
    }

    public function test_empty_and_single_page_lists_have_no_navigation(): void
    {
        foreach ([new LengthAwarePaginator([], 0, 12), new LengthAwarePaginator([1], 1, 12), new Paginator([1], 12)] as $paginator) {
            $this->assertSame('', trim($this->renderPagination($paginator)));
        }
    }

    public function test_simple_last_page_keeps_previous_navigation(): void
    {
        $html = $this->renderPagination(new Paginator([1], 12, 3, ['path' => '/noticias']));
        $this->assertStringContainsString('rel="prev"', $html);
        $this->assertStringNotContainsString('rel="next"', $html);
        $this->assertStringContainsString('aria-disabled="true"', $html);
    }

    public function test_default_backend_pagination_still_uses_bootstrap(): void
    {
        $html = (new LengthAwarePaginator(range(1, 12), 24, 12))->links()->render();
        $this->assertStringContainsString('page-link', $html);
        $this->assertStringNotContainsString('data-public-pagination', $html);
    }
}
