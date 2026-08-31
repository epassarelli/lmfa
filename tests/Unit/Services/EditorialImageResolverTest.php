<?php

namespace Tests\Unit\Services;

use App\Models\Album;
use App\Models\Comida;
use App\Models\Event;
use App\Models\Festival;
use App\Models\Interprete;
use App\Models\KnowledgeArticle;
use App\Models\MediaAsset;
use App\Models\Mito;
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

    public function test_event_can_reuse_loaded_artist_media(): void
    {
        $event = new Event(['title' => 'Peña sin imagen']);
        $event->setRelation('images', new Collection());

        $artist = new Interprete(['interprete' => 'Artista del evento']);
        $media = new MediaAsset(['alt' => 'Foto artista']);
        $artist->setRelation('images', new Collection([$media]));

        $event->setRelation('interpretes', new Collection([$artist]));

        $resolved = app(EditorialImageResolver::class)->resolve($event);

        $this->assertSame($media, $resolved->media);
        $this->assertSame('related', $resolved->sourceType);
        $this->assertSame(Interprete::class, $resolved->sourceEntity);
    }

    public function test_festival_prefers_artist_before_related_event(): void
    {
        $festival = new Festival(['title' => 'Festival de prueba']);
        $festival->setRelation('images', new Collection());

        $artist = new Interprete(['interprete' => 'Artista de festival']);
        $artistMedia = new MediaAsset(['alt' => 'Artista']);
        $artist->setRelation('images', new Collection([$artistMedia]));

        $event = new Event(['title' => 'Evento relacionado']);
        $eventMedia = new MediaAsset(['alt' => 'Evento']);
        $event->setRelation('images', new Collection([$eventMedia]));

        $festival->setRelation('interpretes', new Collection([$artist]));
        $festival->setRelation('events', new Collection([$event]));

        $resolved = app(EditorialImageResolver::class)->resolve($festival);

        $this->assertSame($artistMedia, $resolved->media);
        $this->assertSame(Interprete::class, $resolved->sourceEntity);
    }

    public function test_evergreen_can_reuse_loaded_related_media(): void
    {
        $article = new KnowledgeArticle(['title' => 'Historia de la chacarera']);
        $article->setRelation('images', new Collection());

        $festival = new Festival(['title' => 'Festival relacionado']);
        $media = new MediaAsset(['alt' => 'Festival']);
        $festival->setRelation('images', new Collection([$media]));

        $article->setRelation('interpretes', new Collection());
        $article->setRelation('festivales', new Collection([$festival]));

        $resolved = app(EditorialImageResolver::class)->resolve($article);

        $this->assertSame($media, $resolved->media);
        $this->assertSame(Festival::class, $resolved->sourceEntity);
    }

    public function test_it_supports_legacy_photo_paths_without_migrating_the_entity(): void
    {
        $album = new Album(['album' => 'Disco legacy', 'foto' => 'cover.jpg']);
        $album->setRelation('images', new Collection());

        $recipe = new Comida(['titulo' => 'Locro', 'foto' => 'locro.jpg']);
        $recipe->setRelation('images', new Collection());

        $myth = new Mito(['titulo' => 'Leyenda', 'foto' => 'leyenda.jpg']);
        $myth->setRelation('images', new Collection());

        $resolver = app(EditorialImageResolver::class);

        $this->assertStringContainsString('storage/albunes/cover.jpg', $resolver->resolve($album)->url);
        $this->assertStringContainsString('storage/comidas/locro.jpg', $resolver->resolve($recipe)->url);
        $this->assertStringContainsString('storage/mitos/leyenda.jpg', $resolver->resolve($myth)->url);
    }

    public function test_it_does_not_query_unloaded_relations_and_uses_safe_fallback(): void
    {
        $news = new News(['title' => 'Sin ninguna imagen', 'categoria_id' => 1]);

        $resolved = app(EditorialImageResolver::class)->resolve($news);

        $this->assertTrue($resolved->isFallback());
        $this->assertSame('fallback', $resolved->sourceType);
        $this->assertStringContainsString('img/logo-share.jpg', $resolved->url);
    }
}
