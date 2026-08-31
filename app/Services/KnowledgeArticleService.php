<?php

namespace App\Services;

use App\Models\KnowledgeArticle;
use App\Models\User;
use App\Support\KnowledgeArticleBodySanitizer;
use App\Support\ImageSourceMetadata;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class KnowledgeArticleService
{
    public function __construct(
        protected ImageUploadService $imageService,
        protected ImageSourceResolver $imageResolver
    ) {
    }

    public function createArticle(array $data, mixed $image = null): KnowledgeArticle
    {
        return DB::transaction(function () use ($data, $image) {
            $data = $this->normalizeData($data, true);

            $article = KnowledgeArticle::create($this->extractArticleAttributes($data));

            $this->syncRelations($article, $data);
            $this->processImage($article, $image, false, $data['image_alt'] ?? null);

            return $article->fresh($this->defaultRelations());
        });
    }

    public function updateArticle(KnowledgeArticle $article, array $data, mixed $image = null): KnowledgeArticle
    {
        return DB::transaction(function () use ($article, $data, $image) {
            $data = $this->normalizeData($data, false, $article);

            $article->update($this->extractArticleAttributes($data));

            $this->syncRelations($article, $data);
            $this->processImage($article, $image, true, $data['image_alt'] ?? null);

            return $article->fresh($this->defaultRelations());
        });
    }

    public function publish(KnowledgeArticle $article, ?User $reviewer = null): KnowledgeArticle
    {
        $article->publish($reviewer);

        return $article->fresh($this->defaultRelations());
    }

    public function unpublish(KnowledgeArticle $article, ?User $reviewer = null): KnowledgeArticle
    {
        $article->unpublish($reviewer);

        return $article->fresh($this->defaultRelations());
    }

    public function archive(KnowledgeArticle $article, ?User $reviewer = null): void
    {
        $article->forceFill([
            'editorial_status' => 'archived',
            'reviewed_by' => $reviewer?->id ?? $article->reviewed_by,
        ])->save();

        $article->delete();
    }

    public function defaultRelations(): array
    {
        return [
            'category',
            'author',
            'reviewer',
            'interpretes',
            'canciones.interprete',
            'albums.interprete',
            'festivales.provincia',
            'events.provincia',
            'provincias',
            'relatedArticles.category',
            'images',
        ];
    }

    protected function normalizeData(array $data, bool $isCreating, ?KnowledgeArticle $article = null): array
    {
        $data['slug'] = Str::slug($data['slug'] ?? $data['title'] ?? $article?->title ?? 'enciclopedia-'.now()->timestamp);
        $data['author_id'] = $data['author_id'] ?? auth()->id();
        $data['editorial_status'] = $data['editorial_status'] ?? ($article?->editorial_status ?? 'draft');

        if (array_key_exists('body', $data)) {
            $data['body'] = KnowledgeArticleBodySanitizer::normalize($data['body']);
        }

        if ($data['editorial_status'] === 'published' && empty($data['published_at'])) {
            $data['published_at'] = $article?->published_at ?? now();
        }

        if ($isCreating && ! isset($data['last_verified_at'])) {
            $data['last_verified_at'] = now();
        }

        return $data;
    }

    protected function extractArticleAttributes(array $data): array
    {
        return collect($data)->only([
            'knowledge_category_id',
            'title',
            'slug',
            'excerpt',
            'body',
            'image_alt',
            'seo_title',
            'meta_description',
            'primary_keyword',
            'secondary_keywords',
            'editorial_status',
            'published_at',
            'last_verified_at',
            'author_id',
            'reviewed_by',
        ])->toArray();
    }

    protected function syncRelations(KnowledgeArticle $article, array $data): void
    {
        $article->interpretes()->sync($data['interprete_ids'] ?? []);
        $article->canciones()->sync($data['cancion_ids'] ?? []);
        $article->albums()->sync($data['album_ids'] ?? []);
        $article->festivales()->sync($data['festival_ids'] ?? []);
        $article->events()->sync($data['event_ids'] ?? []);
        $article->provincias()->sync($data['provincia_ids'] ?? []);

        $relatedIds = collect($data['related_article_ids'] ?? [])
            ->filter(fn ($id) => (int) $id !== (int) $article->id)
            ->unique()
            ->values()
            ->all();

        $article->relatedArticles()->sync($relatedIds);
    }

    protected function processImage(KnowledgeArticle $article, mixed $image, bool $replace, ?string $imageAlt = null): void
    {
        $resolved = null;

        try {
            $resolved = $this->imageResolver->resolve($image);

            if (! $resolved instanceof UploadedFile) {
                return;
            }

            $media = $this->imageService->process(
                $resolved,
                $article,
                'news_full',
                'enciclopedia',
                $replace,
                $article->slug,
                array_merge(
                    ImageSourceMetadata::from($image),
                    $imageAlt ? ['alt' => $imageAlt] : []
                )
            );

            $article->forceFill([
                'featured_image_id' => $media->id,
                'featured_image_path' => $media->path,
                'image_alt' => $imageAlt ?: ($article->image_alt ?: $article->title),
            ])->save();

            if ($imageAlt) {
                $media->update(['alt' => $imageAlt]);
            }
        } finally {
            $this->imageResolver->cleanupTemporary($resolved);
        }
    }
}
