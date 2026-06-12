<?php

namespace Tests\Feature\News;

use App\Models\Categoria;
use App\Models\MediaAsset;
use App\Models\News;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class NewsImageFallbackTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
    }

    private function category(): Categoria
    {
        return Categoria::first() ?: Categoria::create([
            'nombre' => 'General',
            'slug' => 'general',
        ]);
    }

    /** @test */
    public function home_prefers_media_asset_variants_for_published_news(): void
    {
        $category = $this->category();

        $news = News::create([
            'title' => 'News with media asset',
            'slug' => 'news-with-media-asset',
            'body' => 'Contenido de prueba',
            'categoria_id' => $category->id,
            'editorial_status' => 'published',
            'estado' => 1,
        ]);

        Storage::disk('public')->put('news/2026/06/news-with-media-asset_card_320.webp', 'fake-webp');

        MediaAsset::create([
            'imageable_type' => News::class,
            'imageable_id' => $news->id,
            'disk' => 'public',
            'original_name' => 'news.jpg',
            'original_path' => 'news/2026/06/news-with-media-asset_original.jpg',
            'mime' => 'image/jpeg',
            'size' => 123,
            'profile' => 'news_full',
            'variants_json' => [
                'card' => [
                    320 => 'news/2026/06/news-with-media-asset_card_320.webp',
                ],
            ],
        ]);

        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSee('news/2026/06/news-with-media-asset_card_320.webp', false);
    }

    /** @test */
    public function noticia_alias_can_resolve_news_media_relation(): void
    {
        $category = $this->category();

        $news = News::create([
            'title' => 'Alias media relation',
            'slug' => 'alias-media-relation',
            'body' => 'Contenido de prueba',
            'categoria_id' => $category->id,
            'editorial_status' => 'published',
            'estado' => 1,
        ]);

        MediaAsset::create([
            'imageable_type' => News::class,
            'imageable_id' => $news->id,
            'disk' => 'public',
            'original_name' => 'news.jpg',
            'original_path' => 'news/2026/06/alias-media-relation_original.jpg',
            'mime' => 'image/jpeg',
            'size' => 123,
            'profile' => 'news_full',
            'variants_json' => ['card' => [320 => 'news/2026/06/alias-media-relation_card_320.webp']],
        ]);

        $legacyQuery = \App\Models\Noticia::with('images')->findOrFail($news->id);

        $this->assertCount(1, $legacyQuery->images);
    }

    /** @test */
    public function noticia_card_falls_back_to_legacy_storage_image_when_media_assets_are_missing(): void
    {
        $category = $this->category();

        Storage::disk('public')->put('noticias/legacy-cover.jpg', 'fake-jpg');

        News::create([
            'title' => 'Legacy fallback news',
            'slug' => 'legacy-fallback-news',
            'body' => 'Contenido de prueba',
            'categoria_id' => $category->id,
            'editorial_status' => 'published',
            'estado' => 1,
            'featured_image_path' => 'legacy-cover.jpg',
        ]);

        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSee(Storage::disk('public')->url('noticias/legacy-cover.jpg'), false);
    }
}
