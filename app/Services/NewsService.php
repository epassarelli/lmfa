<?php

namespace App\Services;

use App\Models\News;
use App\Support\NewsImagePathResolver;
use App\Support\RichTextHeadingSanitizer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class NewsService
{
    protected $imageService;

    protected $imageResolver;

    public function __construct(ImageUploadService $imageService, ImageSourceResolver $imageResolver)
    {
        $this->imageService = $imageService;
        $this->imageResolver = $imageResolver;
    }

    /**
     * Crea una nueva noticia centralizando la lógica de negocio.
     */
    public function createNews(array $data, $image = null): News
    {
        $resolvedImage = null;
        try {
            return DB::transaction(function () use ($data, $image, &$resolvedImage) {
                // 1. Preparar datos básicos y autoría
                $data['created_by'] = $data['created_by'] ?? auth()->id();
                $data['approved_by'] = $data['approved_by'] ?? null;

                if (empty($data['slug'])) {
                    $data['slug'] = Str::slug($data['title'] ?? ($data['titulo'] ?? 'news-'.now()->timestamp));
                }

                // 2. Mapeo de campos legacy
                if (isset($data['publicar'])) {
                    $data['published_at'] = $data['publicar'];
                }

                foreach (['body', 'noticia'] as $field) {
                    if (array_key_exists($field, $data)) {
                        $data[$field] = RichTextHeadingSanitizer::normalize($data[$field]);
                    }
                }

                if (isset($data['interprete_principal_id'])) {
                    $data['interprete_id'] = $data['interprete_principal_id'];
                }

                if (! isset($data['editorial_status']) && ! auth()->user()->canPublish()) {
                    $data['editorial_status'] = 'draft';
                }

                // 3. Crear la noticia
                unset($data['foto']);
                if (! isset($data['editorial_status'])) {
                    $data['editorial_status'] = array_key_exists('estado', $data)
                        ? ($data['estado'] ? 'published' : 'draft')
                        : 'draft';
                }

                if ($data['editorial_status'] === 'published' && empty($data['published_at'])) {
                    $data['published_at'] = now();
                }

                $news = News::create($data);

                // 4. Sincronizar intérpretes secundarios
                if (! empty($data['interprete_secundarios'])) {
                    $news->interpretes()->sync(
                        $this->sanitizeSecondaryInterpretes(
                            $data['interprete_secundarios'],
                            $news->interprete_id
                        )
                    );
                }

                // 5. Procesar imagen
                $resolvedImage = $this->imageResolver->resolve($image);
                if ($resolvedImage) {
                    $media = $this->imageService->process(
                        $resolvedImage,
                        $news,
                        'news_full',
                        'news',
                        false,
                        $news->slug
                    );

                    $news->forceFill([
                        'featured_image_path' => NewsImagePathResolver::toLegacyStoredPath($media->path),
                    ])->save();
                }

                return $news;
            });
        } finally {
            $this->cleanupTemporaryImage($resolvedImage);
        }
    }

    /**
     * Actualiza una noticia existente.
     */
    public function updateNews(News $news, array $data, $image = null): News
    {
        $resolvedImage = null;
        try {
            return DB::transaction(function () use ($news, $data, $image, &$resolvedImage) {
                // 1. Mapeo de campos legacy
                if (isset($data['publicar'])) {
                    $data['published_at'] = $data['publicar'];
                }

                foreach (['body', 'noticia'] as $field) {
                    if (array_key_exists($field, $data)) {
                        $data[$field] = RichTextHeadingSanitizer::normalize($data[$field]);
                    }
                }

                if (isset($data['interprete_principal_id'])) {
                    $data['interprete_id'] = $data['interprete_principal_id'];
                }

                if (! isset($data['editorial_status']) && ! auth()->user()->canPublish()) {
                    $data['editorial_status'] = 'draft';
                }

                // 2. Actualizar
                unset($data['foto']);
                if (! isset($data['editorial_status']) && isset($data['estado'])) {
                    $data['editorial_status'] = $data['estado'] ? 'published' : 'draft';
                }
                if (($data['editorial_status'] ?? null) === 'published' && empty($data['published_at']) && empty($news->published_at)) {
                    $data['published_at'] = now();
                }
                $news->update($data);

                // 3. Sincronizar intérpretes
                if (isset($data['interprete_secundarios'])) {
                    $news->interpretes()->sync(
                        $this->sanitizeSecondaryInterpretes(
                            $data['interprete_secundarios'],
                            $news->interprete_id
                        )
                    );
                }

                // 4. Procesar nueva imagen
                $resolvedImage = $this->imageResolver->resolve($image);
                if ($resolvedImage) {
                    $media = $this->imageService->process(
                        $resolvedImage,
                        $news,
                        'news_full',
                        'news',
                        true, // Reemplazar
                        $news->slug
                    );

                    $news->forceFill([
                        'featured_image_path' => NewsImagePathResolver::toLegacyStoredPath($media->path),
                    ])->save();
                }

                return $news;
            });
        } finally {
            $this->cleanupTemporaryImage($resolvedImage);
        }
    }

    /**
     * Limpia el archivo temporal si fue generado por el resolver (descarga de URL o similar).
     */
    protected function cleanupTemporaryImage($image): void
    {
        if ($image instanceof \Illuminate\Http\UploadedFile) {
            $path = $image->getPathname();
            if (str_contains($path, 'tmp/news-images')) {
                @unlink($path);
            }
        }
    }

    protected function sanitizeSecondaryInterpretes(array $interpreteIds, ?int $primaryInterpreteId): array
    {
        return collect($interpreteIds)
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->reject(fn (int $id) => $primaryInterpreteId !== null && $id === (int) $primaryInterpreteId)
            ->unique()
            ->values()
            ->all();
    }
}
