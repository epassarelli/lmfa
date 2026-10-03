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
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class EditorialImageResolverTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
    }

    private function storedMedia(string $alt): MediaAsset
    {
        $path = 'tests/'.uniqid().'_card_480.webp';
        Storage::disk('public')->put($path, 'img');

        return new MediaAsset([
            'alt' => $alt,
            'disk' => 'public',
            'original_path' => $path,
            'variants_json' => ['card' => [480 => $path]],
        ]);
    }

    public function test_media_whose_files_are_missing_on_disk_falls_back(): void
    {
        $event = new Event(['title' => 'Evento con media rota']);
        $event->setRelation('images', new Collection([new MediaAsset([
            'alt' => 'Rota',
            'disk' => 'public',
            'original_path' => 'events/no-existe_original.png',
            'variants_json' => ['card' => [480 => 'events/no-existe_card_480.webp']],
        ])]));

        $resolved = app(EditorialImageResolver::class)->resolve($event);

        $this->assertTrue($resolved->isFallback());
        $this->assertStringContainsString(config('editorial_images.fallbacks.event'), $resolved->url);
    }

    public function test_media_with_an_empty_variant_group_is_valid_if_another_group_exists(): void
    {
        Storage::disk('public')->put('events/sidebar_120.webp', 'img');
        $event = new Event(['title' => 'Evento con variantes parciales']);
        $media = new MediaAsset([
            'disk' => 'public',
            'original_path' => 'events/no-existe_original.png',
            'variants_json' => ['card' => [], 'sidebar' => [120 => 'events/sidebar_120.webp']],
        ]);
        $event->setRelation('images', new Collection([$media]));

        $this->assertSame($media, app(EditorialImageResolver::class)->resolve($event)->media);
    }

    public function test_legacy_paths_missing_on_disk_fall_back_and_event_skips_a_broken_artist_photo(): void
    {
        $artist = new Interprete(['interprete' => 'Artista sin archivo', 'foto' => 'no-existe.jpg']);
        $artist->setRelation('images', new Collection());

        $event = new Event(['title' => 'Evento sin imagen', 'featured_image_path' => 'events/tampoco-existe.jpg']);
        $event->setRelation('images', new Collection());
        $event->setRelation('interpretes', new Collection([$artist]));

        $resolver = app(EditorialImageResolver::class);

        $this->assertTrue($resolver->resolve($artist)->isFallback());
        $this->assertStringContainsString(config('editorial_images.fallbacks.artist'), $resolver->resolve($artist)->url);
        $this->assertTrue($resolver->resolve($event)->isFallback());
        $this->assertStringContainsString(config('editorial_images.fallbacks.event'), $resolver->resolve($event)->url);
    }

    public function test_existing_artist_photo_is_used_and_external_urls_are_trusted(): void
    {
        Storage::disk('public')->put('interpretes/existe.jpg', 'img');
        $artist = new Interprete(['interprete' => 'Artista con foto', 'foto' => 'existe.jpg']);
        $artist->setRelation('images', new Collection());

        $event = new Event(['title' => 'Evento externo', 'featured_image_path' => 'https://cdn.example.test/evento.jpg']);
        $event->setRelation('images', new Collection());

        $resolver = app(EditorialImageResolver::class);

        $this->assertStringContainsString('storage/interpretes/existe.jpg', $resolver->resolve($artist)->url);
        $this->assertSame('https://cdn.example.test/evento.jpg', $resolver->resolve($event)->url);
    }

    public function test_it_prefers_own_media_asset(): void
    {
        $news = new News(['title' => 'Noticia con imagen', 'categoria_id' => 1]);
        $media = $this->storedMedia('Alt propio');
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
        $media = $this->storedMedia('Foto del artista');
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
        $media = $this->storedMedia('Foto artista');
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
        $artistMedia = $this->storedMedia('Artista');
        $artist->setRelation('images', new Collection([$artistMedia]));

        $event = new Event(['title' => 'Evento relacionado']);
        $eventMedia = $this->storedMedia('Evento');
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
        $media = $this->storedMedia('Festival');
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

        foreach (['albunes/cover.jpg', 'comidas/locro.jpg', 'mitos/leyenda.jpg'] as $path) {
            Storage::disk('public')->put($path, 'img');
        }

        $resolver = app(EditorialImageResolver::class);

        $this->assertStringContainsString('storage/albunes/cover.jpg', $resolver->resolve($album)->url);
        $this->assertStringContainsString('storage/comidas/locro.jpg', $resolver->resolve($recipe)->url);
        $this->assertStringContainsString('storage/mitos/leyenda.jpg', $resolver->resolve($myth)->url);
    }

    public function test_all_configured_fallback_assets_exist_in_public_directory(): void
    {
        $paths = collect(config('editorial_images.fallbacks'))
            ->flatMap(function ($value) {
                return is_array($value) ? array_values($value) : [$value];
            })
            ->unique()
            ->values();

        foreach ($paths as $path) {
            $this->assertFileExists(public_path($path), "Missing fallback asset: {$path}");
        }
    }

    public function test_it_does_not_query_unloaded_relations_and_uses_safe_fallback(): void
    {
        $news = new News(['title' => 'Sin ninguna imagen', 'categoria_id' => 1]);

        $resolved = app(EditorialImageResolver::class)->resolve($news);

        $this->assertTrue($resolved->isFallback());
        $this->assertSame('fallback', $resolved->sourceType);
        $this->assertStringContainsString('img/fallbacks/news-actualidad-v2.webp', $resolved->url);
    }

    public function test_album_fallback_alt_uses_artist_name_not_a_dumped_model(): void
    {
        // Regresion: Album::interprete() es una relacion belongsTo, asi que
        // $entity->interprete resuelve al modelo Interprete, no a un string.
        // El alt de fallback debia extraer el nombre, no volcar el modelo entero.
        $artist = new Interprete(['interprete' => 'Los Huayra']);
        $album = new Album(['album' => 'Sin imagen propia']);
        $album->setRelation('interprete', $artist);
        $album->setRelation('images', new Collection());

        $resolved = app(EditorialImageResolver::class)->resolve($album);

        $this->assertTrue($resolved->isFallback());
        $this->assertSame('Los Huayra', $resolved->alt);
    }
}
