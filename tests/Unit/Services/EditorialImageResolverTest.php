<?php

namespace Tests\Unit\Services;

use App\Models\Interprete;
use App\Models\MediaAsset;
use App\Models\News;
use App\Services\EditorialImageResolver;
use Illuminate\Support\Collection;
use Tests\TestCase;

class EditorialImageResolverTest extends TestCase
{
    public function test_it_prefers_own_media_asset(): void
    {
        $news = new News(['title' => 'Noticia con imagen', 'categoria_id' => 1]);
        $media = new MediaAsset(['alt' => 'Alt propio']);
        $news->setRelation('images', new Collection([$media]));

        $resolved = app(EditorialImageResolver::class)->resolve($news);

        $this->assertTrue($resolved->isMedia());
        $this->assertSame($media, $resolved->media);
        $this->assertSame('own_media', $resolved->sourceType);
        $this->assertSame('Alt propio', $resolved->alt);
    }

    public function test_news_can_reuse_loaded_artist_media(): void
    {
        $news = new News(['title' => 'Noticia sin imagen', 'categoria_id' => 1]);
        $news->setRelation('images', new Collection());

        $artist = new Interprete(['interprete' => 'Artista relacionado']);
        $media = new MediaAsset(['alt' => 'Foto del artista']);
        $artist->setRelation('images', new Collection([$media]));

        $news->setRelation('interprete', $artist);

        $resolved = app(EditorialImageResolver::class)->resolve($news);

        $this->assertSame($media, $resolved->media);
        $this->assertSame('related', $resolved->sourceType);
        $this->assertSame(Interprete::class, $resolved->sourceEntity);
        $this->assertSame('Noticia sin imagen', $resolved->alt);
    }

    public function test_it_does_not_query_unloaded_relations_and_uses_fallback(): void
    {
        $news = new News(['title' => 'Sin ninguna imagen', 'categoria_id' => 1]);

        $resolved = app(EditorialImageResolver::class)->resolve($news);

        $this->assertTrue($resolved->isFallback());
        $this->assertSame('fallback', $resolved->sourceType);
        $this->assertStringContainsString('img/fallbacks/news-actualidad.webp', $resolved->url);
    }
}
