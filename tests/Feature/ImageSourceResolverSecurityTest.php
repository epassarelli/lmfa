<?php

namespace Tests\Feature;

use App\Exceptions\ImageInvalidException;
use App\Exceptions\ImageSecurityException;
use App\Services\ImageSourceResolver;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Cobertura directa de la resolución segura de imágenes externas (SSRF, tipo y tamaño). Antes solo se
 * ejercitaba de forma indirecta a través de la aprobación de contribuciones, flujo ya retirado.
 */
class ImageSourceResolverSecurityTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
    }

    public function test_it_blocks_loopback_and_private_addresses_without_making_requests(): void
    {
        Http::fake();

        foreach (['http://127.0.0.1/admin/config', 'http://localhost/foto.jpg', 'http://10.0.0.5/foto.jpg', 'http://192.168.1.10/foto.jpg', 'http://169.254.169.254/latest/meta-data'] as $url) {
            try {
                app(ImageSourceResolver::class)->resolve($url);
                $this->fail("Debía bloquear $url");
            } catch (ImageSecurityException $e) {
                $this->assertStringContainsString('bloqueado', $e->getMessage());
            }
        }

        Http::assertNothingSent();
    }

    public function test_it_rejects_non_http_schemes(): void
    {
        $this->expectException(ImageSecurityException::class);

        app(ImageSourceResolver::class)->resolve('ftp://ejemplo.com/foto.jpg');
    }

    public function test_it_downloads_an_external_image_and_returns_an_uploaded_file(): void
    {
        $image = UploadedFile::fake()->image('externa.jpg');
        Http::fake(['https://93.184.216.34/foto.jpg' => Http::response(file_get_contents($image->getPathname()), 200, ['Content-Type' => 'image/jpeg'])]);

        $file = app(ImageSourceResolver::class)->resolve('https://93.184.216.34/foto.jpg');

        $this->assertInstanceOf(UploadedFile::class, $file);
        $this->assertSame('image/jpeg', $file->getMimeType());
        $this->assertFileExists($file->getPathname());
    }

    public function test_it_rejects_urls_that_are_not_images(): void
    {
        Http::fake(['https://93.184.216.34/pagina' => Http::response('<html></html>', 200, ['Content-Type' => 'text/html'])]);

        $this->expectException(ImageInvalidException::class);

        app(ImageSourceResolver::class)->resolve('https://93.184.216.34/pagina');
    }
}
