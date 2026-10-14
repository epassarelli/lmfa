<?php

namespace Tests\Feature;

use Illuminate\Http\Request;
use Tests\TestCase;

/**
 * Regresión: /csrf-refresh devolvía 404 porque la ruta comodín /{interprete:slug} (registrada antes) la capturaba.
 * Sin esa ruta, los formularios de páginas cacheadas (p. ej. suscripción al newsletter del sidebar) enviaban
 * el token vacío y fallaban con 419.
 */
class CsrfRefreshRouteTest extends TestCase
{
    public function test_csrf_refresh_resolves_to_its_route_and_not_to_the_artist_wildcard(): void
    {
        $route = app('router')->getRoutes()->match(Request::create('/csrf-refresh', 'GET'));

        $this->assertSame('csrf.refresh', $route->getName());
    }

    public function test_csrf_refresh_returns_a_token_as_json(): void
    {
        $response = $this->get('/csrf-refresh')->assertOk()->assertHeader('Content-Type', 'application/json');

        $this->assertNotEmpty($response->json('token'));
        $this->assertSame(csrf_token(), $response->json('token'));
    }

    public function test_public_single_segment_routes_are_not_swallowed_by_the_artist_wildcard(): void
    {
        foreach (['/csrf-refresh' => 'csrf.refresh', '/como-publicar' => 'publish.guide', '/privacidad' => 'legal.privacy', '/condiciones' => 'legal.terms'] as $path => $name) {
            $this->assertSame($name, app('router')->getRoutes()->match(Request::create($path, 'GET'))->getName(), "$path quedó tapada por una ruta comodín.");
        }
    }

    public function test_the_newsletter_form_page_ships_an_empty_token_that_the_refresh_endpoint_fills(): void
    {
        // El formulario viaja con _token vacío (la página se cachea) y el JS lo completa con /csrf-refresh.
        $this->get('/noticias-del-folklore-argentino')->assertOk()->assertSee('data-csrf-refresh', false);
        $this->get('/csrf-refresh')->assertOk();
    }
}
