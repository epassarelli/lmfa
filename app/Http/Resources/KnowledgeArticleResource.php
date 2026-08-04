<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class KnowledgeArticleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'excerpt' => $this->excerpt,
            'body' => $this->body,
            'image_alt' => $this->image_alt,
            'featured_image_id' => $this->featured_image_id,
            'featured_image_path' => $this->featured_image_path,
            'seo_title' => $this->seo_title,
            'meta_description' => $this->meta_description,
            'primary_keyword' => $this->primary_keyword,
            'secondary_keywords' => $this->secondary_keywords,
            'editorial_status' => $this->editorial_status,
            'published_at' => optional($this->published_at)->toIso8601String(),
            'last_verified_at' => optional($this->last_verified_at)->toIso8601String(),
            'visits' => $this->visits,
            'url' => $this->when(
                $this->relationLoaded('category') && $this->category,
                fn () => $this->getUrl()
            ),
            'category' => new KnowledgeCategoryResource($this->whenLoaded('category')),
            'author' => $this->whenLoaded('author', fn () => [
                'id' => $this->author?->id,
                'name' => $this->author?->name,
            ]),
            'reviewer' => $this->whenLoaded('reviewer', fn () => [
                'id' => $this->reviewer?->id,
                'name' => $this->reviewer?->name,
            ]),
            'relationships' => [
                'interpretes' => $this->whenLoaded('interpretes', fn () => $this->interpretes->map(fn ($item) => [
                    'id' => $item->id,
                    'name' => $item->interprete,
                    'slug' => $item->slug,
                ])),
                'canciones' => $this->whenLoaded('canciones', fn () => $this->canciones->map(fn ($item) => [
                    'id' => $item->id,
                    'name' => $item->cancion,
                    'slug' => $item->slug,
                ])),
                'albums' => $this->whenLoaded('albums', fn () => $this->albums->map(fn ($item) => [
                    'id' => $item->id,
                    'name' => $item->album,
                    'slug' => $item->slug,
                ])),
                'festivales' => $this->whenLoaded('festivales', fn () => $this->festivales->map(fn ($item) => [
                    'id' => $item->id,
                    'name' => $item->titulo,
                    'slug' => $item->slug,
                ])),
                'events' => $this->whenLoaded('events', fn () => $this->events->map(fn ($item) => [
                    'id' => $item->id,
                    'name' => $item->title,
                    'slug' => $item->slug,
                ])),
                'provincias' => $this->whenLoaded('provincias', fn () => $this->provincias->map(fn ($item) => [
                    'id' => $item->id,
                    'name' => $item->nombre,
                    'slug' => $item->slug,
                ])),
                'related_articles' => $this->whenLoaded('relatedArticles', fn () => $this->relatedArticles->map(fn ($item) => [
                    'id' => $item->id,
                    'title' => $item->title,
                    'slug' => $item->slug,
                ])),
            ],
            'created_at' => optional($this->created_at)->toIso8601String(),
            'updated_at' => optional($this->updated_at)->toIso8601String(),
        ];
    }
}
