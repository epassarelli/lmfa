<?php

namespace App\Services;

use App\Models\Event;
use App\Models\Festival;
use App\Models\Interprete;
use App\Models\KnowledgeArticle;
use App\Models\MediaAsset;
use App\Models\News;
use App\Support\ResolvedEditorialImage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class EditorialImageResolver
{
    public function resolve(Model $entity, bool $allowRelated = true): ResolvedEditorialImage
    {
        if ($media = $this->firstLoadedMedia($entity)) {
            return new ResolvedEditorialImage(
                media: $media,
                url: null,
                alt: $media->alt ?: $this->entityAlt($entity),
                sourceType: 'own_media',
                sourceEntity: $entity::class,
            );
        }

        if ($legacyUrl = $this->legacyUrl($entity)) {
            return new ResolvedEditorialImage(
                media: null,
                url: $legacyUrl,
                alt: $this->entityAlt($entity),
                sourceType: 'own_legacy',
                sourceEntity: $entity::class,
            );
        }

        if ($allowRelated && ($related = $this->relatedEntity($entity))) {
            $resolved = $this->resolve($related, false);

            if (! $resolved->isFallback()) {
                return new ResolvedEditorialImage(
                    media: $resolved->media,
                    url: $resolved->url,
                    alt: $this->entityAlt($entity),
                    sourceType: 'related',
                    sourceEntity: $related::class,
                );
            }
        }

        return new ResolvedEditorialImage(
            media: null,
            url: asset($this->fallbackPath($entity)),
            alt: $this->entityAlt($entity),
            sourceType: 'fallback',
            sourceEntity: null,
        );
    }

    private function firstLoadedMedia(Model $entity): ?MediaAsset
    {
        if (! $entity->relationLoaded('images')) {
            return null;
        }

        $images = $entity->getRelation('images');

        if ($images instanceof Collection) {
            return $images->first();
        }

        return null;
    }

    private function relatedEntity(Model $entity): ?Model
    {
        return match (true) {
            $entity instanceof News => $this->loadedBelongsTo($entity, 'interprete')
                ?? $this->firstLoadedFrom($entity, 'festivales'),

            $entity instanceof Event => $this->firstLoadedFrom($entity, 'interpretes')
                ?? $this->firstLoadedFrom($entity, 'festivales'),

            $entity instanceof Festival => $this->firstLoadedFrom($entity, 'interpretes')
                ?? $this->firstLoadedFrom($entity, 'events'),

            $entity instanceof KnowledgeArticle => $this->firstLoadedFrom($entity, 'interpretes')
                ?? $this->firstLoadedFrom($entity, 'festivales')
                ?? $this->firstLoadedFrom($entity, 'events'),

            default => null,
        };
    }

    private function loadedBelongsTo(Model $entity, string $relation): ?Model
    {
        if (! $entity->relationLoaded($relation)) {
            return null;
        }

        $related = $entity->getRelation($relation);

        return $related instanceof Model ? $related : null;
    }

    private function firstLoadedFrom(Model $entity, string $relation): ?Model
    {
        if (! $entity->relationLoaded($relation)) {
            return null;
        }

        $related = $entity->getRelation($relation);

        return $related instanceof Collection
            ? $related->first(fn ($item) => $item instanceof Model)
            : null;
    }

    private function legacyUrl(Model $entity): ?string
    {
        if ($entity instanceof News) {
            return $entity->legacy_featured_image_url;
        }

        if ($entity instanceof Interprete && filled($entity->foto)) {
            return Storage::disk('public')->url('interpretes/'.ltrim($entity->foto, '/'));
        }

        if (($entity instanceof Event || $entity instanceof Festival || $entity instanceof KnowledgeArticle)
            && filled($entity->featured_image_path)) {
            $path = preg_replace('#^/?storage/#', '', trim((string) $entity->featured_image_path));

            if (filter_var($entity->featured_image_path, FILTER_VALIDATE_URL)) {
                return $entity->featured_image_path;
            }

            return filled($path) ? Storage::disk('public')->url($path) : null;
        }

        return null;
    }

    private function fallbackPath(Model $entity): string
    {
        if ($entity instanceof News) {
            return config(
                'editorial_images.fallbacks.news.'.$entity->categoria_id,
                config('editorial_images.fallbacks.news.default')
            );
        }

        return match (true) {
            $entity instanceof Event => config('editorial_images.fallbacks.event'),
            $entity instanceof Festival => config('editorial_images.fallbacks.festival'),
            $entity instanceof Interprete => config('editorial_images.fallbacks.artist'),
            $entity instanceof KnowledgeArticle => config('editorial_images.fallbacks.knowledge'),
            default => config('editorial_images.fallbacks.default'),
        };
    }

    private function entityAlt(Model $entity): string
    {
        return trim((string) (
            $entity->image_alt
            ?? $entity->title
            ?? $entity->titulo
            ?? $entity->interprete
            ?? $entity->name
            ?? 'Mi Folklore Argentino'
        ));
    }
}
