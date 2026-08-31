<?php

namespace Tests\Unit\Support;

use App\Support\ApiImageInput;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class ApiImageInputTest extends TestCase
{
    public function test_explicit_featured_image_url_is_used_and_removed_from_payload(): void
    {
        $request = Request::create('/api/test', 'POST', [
            'featured_image_url' => 'https://example.com/image.jpg',
            'featured_image_path' => 'legacy.jpg',
        ]);

        $payload = [
            'featured_image_url' => 'https://example.com/image.jpg',
            'featured_image_path' => 'legacy.jpg',
        ];

        $source = ApiImageInput::extract($request, $payload, 'featured_image');

        $this->assertSame('https://example.com/image.jpg', $source);
        $this->assertArrayNotHasKey('featured_image_url', $payload);
        $this->assertSame('legacy.jpg', $payload['featured_image_path']);
    }

    public function test_legacy_url_path_is_promoted_to_source_but_relative_path_is_preserved(): void
    {
        $request = Request::create('/api/test', 'POST');

        $urlPayload = ['featured_image_path' => 'https://example.com/legacy.jpg'];
        $source = ApiImageInput::extract($request, $urlPayload, 'featured_image');

        $this->assertSame('https://example.com/legacy.jpg', $source);
        $this->assertArrayNotHasKey('featured_image_path', $urlPayload);

        $pathPayload = ['featured_image_path' => 'news/legacy.jpg'];
        $source = ApiImageInput::extract($request, $pathPayload, 'featured_image');

        $this->assertNull($source);
        $this->assertSame('news/legacy.jpg', $pathPayload['featured_image_path']);
    }

    public function test_uploaded_file_has_highest_precedence(): void
    {
        $file = UploadedFile::fake()->image('cover.jpg');
        $request = Request::create('/api/test', 'POST', [
            'featured_image_url' => 'https://example.com/image.jpg',
        ], [], [
            'featured_image' => $file,
        ]);

        $payload = ['featured_image_url' => 'https://example.com/image.jpg'];

        $source = ApiImageInput::extract($request, $payload, 'featured_image');

        $this->assertSame($file, $source);
        $this->assertArrayNotHasKey('featured_image_url', $payload);
    }
}
