<?php

namespace App\Services;

use App\Models\Event;
use App\Models\Interprete;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class EventService
{
    protected $imageService;

    public function __construct(ImageUploadService $imageService)
    {
        $this->imageService = $imageService;
    }

    /**
     * Crea un nuevo evento centralizando toda la lógica de negocio.
     */
    public function createEvent(array $data, ?UploadedFile $image = null): Event
    {
        return DB::transaction(function () use ($data, $image) {
            // 1. Preparar datos básicos
            $data['created_by'] = $data['created_by'] ?? auth()->id();

            if (empty($data['slug'])) {
                $data['slug'] = Str::slug($data['title'] . '-' . now()->timestamp);
            }

            // 2. Manejar estado editorial si es legacy (mapeo de 'estado' numérico)
            if (isset($data['estado'])) {
                $data['editorial_status'] = $data['estado'] == 1 ? 'published' : 'draft';
                unset($data['estado']);
            }

            // Seguridad: Si el usuario no tiene permisos de publicación, forzar borrador
            if (!auth()->user()->canPublish()) {
                $data['editorial_status'] = 'draft';
            }

            // 3. Crear el evento
            $data['created_by'] = $data['created_by'] ?? auth()->id();
            $event = Event::create($data);

            // 4. Sincronizar intérpretes (si vienen en el request)
            $this->syncInterpretes($event, $data);

            // 5. Procesar imagen si existe
            if ($image) {
                $this->imageService->process(
                    $image,
                    $event,
                    'news_full', // Perfil de imagen
                    'events',    // Carpeta
                    false,       // No reemplazar (es nuevo)
                    $event->slug
                );
            }

            return $event;
        });
    }

    /**
     * Actualiza un evento existente.
     */
    public function updateEvent(Event $event, array $data, ?UploadedFile $image = null): Event
    {
        return DB::transaction(function () use ($event, $data, $image) {
            // 1. Manejar estado editorial
            if (isset($data['estado'])) {
                $data['editorial_status'] = $data['estado'] == 1 ? 'published' : 'draft';
                unset($data['estado']);
            }

            // Seguridad: Si el usuario no tiene permisos de publicación, forzar borrador al editar para re-moderar
            if (!auth()->user()->canPublish()) {
                $data['editorial_status'] = 'draft';
            }

            // 2. Evitar sobrescribir slug si viene vacío
            if (isset($data['slug']) && empty($data['slug'])) {
                unset($data['slug']);
            }

            // 3. Actualizar
            $event->update($data);

            // 4. Sincronizar intérpretes
            $this->syncInterpretes($event, $data);

            // 5. Procesar nueva imagen
            if ($image) {
                $this->imageService->process(
                    $image,
                    $event,
                    'news_full',
                    'events',
                    true, // Reemplazar anterior
                    $event->slug
                );
            }

            return $event;
        });
    }

    /**
     * Helper para sincronizar intérpretes (principal y secundarios).
     */
    protected function syncInterpretes(Event $event, array $data): void
    {
        $interpreteIds = [];

        // Si viene un intérprete principal (legacy 'interprete_id')
        if (!empty($data['interprete_id'])) {
            $interpreteIds[] = $data['interprete_id'];
        }

        // Si vienen intérpretes secundarios o múltiples (array 'interpretes')
        if (!empty($data['interprete_secundarios']) && is_array($data['interprete_secundarios'])) {
            $interpreteIds = array_merge($interpreteIds, $data['interprete_secundarios']);
        }

        if (!empty($interpreteIds)) {
            $event->interpretes()->sync(array_unique($interpreteIds));
        }
    }
}
