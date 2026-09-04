<?php

namespace Tests\Feature\Quality;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class PublicQualityGateTest extends TestCase
{
    use DatabaseTransactions;

    public function test_critical_public_landings_have_accessible_document_structure(): void
    {
        config([
            'features.penia_directory' => true,
            'features.radio_directory' => true,
        ]);

        foreach ($this->criticalPaths() as $path) {
            $response = $this->get($path);
            $html = $response->getContent();

            $response->assertOk();
            $this->assertMatchesRegularExpression('/<html[^>]+lang="[^"]+"/i', $html, $path);
            $this->assertStringContainsString('<title>', $html, $path);
            $this->assertStringContainsString('<main', $html, $path);
            $this->assertStringContainsString('aria-label="Navegacion principal"', $html, $path);
            $this->assertStringContainsString('aria-label="Abrir menu principal"', $html, $path);
            $this->assertSame(1, preg_match_all('/<h1\b/i', $html), $path.' debe contener un unico h1.');
        }
    }

    public function test_critical_public_landings_stay_within_html_response_budget(): void
    {
        config([
            'features.penia_directory' => true,
            'features.radio_directory' => true,
        ]);

        foreach ($this->criticalPaths() as $path) {
            $response = $this->get($path);

            $response->assertOk();
            $this->assertLessThanOrEqual(350 * 1024, strlen($response->getContent()), $path.' excede el presupuesto HTML de 350 KB.');
        }
    }

    private function criticalPaths(): array
    {
        return [
            '/',
            '/noticias-del-folklore-argentino',
            '/festivales-y-fiestas-tradicionales',
            '/penias',
            '/radios-de-folklore-argentino',
        ];
    }
}
