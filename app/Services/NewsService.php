<?php

namespace App\Services;

use App\Models\News;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class NewsService
{
    protected $imageService;

    public function __construct(ImageUploadService $imageService)
    {
        $this->imageService = $imageService;
    }

    /**
     * Crea una nueva noticia centralizando la lógica de negocio.
     */
    public function createNews(array $data, ?UploadedFile $image = null): News
    {
        return DB::transaction(function () use ($data, $image) {
            // 1. Preparar datos básicos
            $data['created_by'] = $data['created_by'] ?? auth()->id();

            if (empty($data['slug'])) {
                $data['slug'] = Str::slug($data['title'] ?? ($data['titulo'] ?? 'news-' . now()->timestamp));
            }

            // 2. Mapeo de campos legacy (si vienen de formularios viejos)
            if (isset($data['publicar'])) {
                $data['published_at'] = $data['publicar'];
            }

            if (isset($data['interprete_principal_id'])) {
                $data['interprete_id'] = $data['interprete_principal_id'];
            }

            // Seguridad: Forzar borrador si el usuario no puede publicar
            if (!auth()->user()->canPublish()) {
                $data['editorial_status'] = 'draft';
            }

            // 3. Crear la noticia (foto es el archivo subido, no va al modelo)
            $data['created_by'] = $data['created_by'] ?? auth()->id();
            unset($data['foto']);
            if (!isset($data['editorial_status'])) {
                $data['editorial_status'] = ($data['estado'] ?? 1) ? 'published' : 'draft';
            }
            $news = News::create($data);

            // 4. Sincronizar intérpretes secundarios
            if (!empty($data['interprete_secundarios'])) {
                $news->interpretes()->sync($data['interprete_secundarios']);
            }

            // 5. Procesar imagen
            if ($image) {
                $this->imageService->process(
                    $image,
                    $news,
                    'news_full',
                    'news',
                    false,
                    $news->slug
                );
            }

            return $news;
        });
    }

    /**
     * Actualiza una noticia existente.
     */
    public function updateNews(News $news, array $data, ?UploadedFile $image = null): News
    {
        return DB::transaction(function () use ($news, $data, $image) {
            // 1. Mapeo de campos legacy
            if (isset($data['publicar'])) {
                $data['published_at'] = $data['publicar'];
            }

            if (isset($data['interprete_principal_id'])) {
                $data['interprete_id'] = $data['interprete_principal_id'];
            }

            // Seguridad: Forzar borrador si el usuario no puede publicar para re-moderar
            if (!auth()->user()->canPublish()) {
                $data['editorial_status'] = 'draft';
            }

            // 2. Actualizar (foto es el archivo subido, no va al modelo)
            unset($data['foto']);
            if (!isset($data['editorial_status']) && isset($data['estado'])) {
                $data['editorial_status'] = $data['estado'] ? 'published' : 'draft';
            }
            $news->update($data);

            // 3. Sincronizar intérpretes
            if (isset($data['interprete_secundarios'])) {
                $news->interpretes()->sync($data['interprete_secundarios']);
            }

            // 4. Procesar nueva imagen
            if ($image) {
                $this->imageService->process(
                    $image,
                    $news,
                    'news_full',
                    'news',
                    true, // Reemplazar
                    $news->slug
                );
            }

            return $news;
        });
    }
}
