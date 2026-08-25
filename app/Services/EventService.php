<?php

namespace App\Services;

use App\Models\Event;
use App\Support\RichTextHeadingSanitizer;
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
     * Crea un nuevo evento centralizando toda la logica de negocio.
     */
    public function createEvent(array $data, ?UploadedFile $image = null): Event
    {
        return DB::transaction(function () use ($data, $image) {
            $data['created_by'] = $data['created_by'] ?? auth()->id();

            foreach (['body', 'detalles'] as $field) {
                if (array_key_exists($field, $data)) {
                    $data[$field] = RichTextHeadingSanitizer::normalize($data[$field]);
                }
            }

            if (empty($data['slug'])) {
                $data['slug'] = Str::slug($data['title'].'-'.now()->timestamp);
            }

            if (isset($data['estado'])) {
                $data['editorial_status'] = $data['estado'] == 1 ? 'published' : 'draft';
                unset($data['estado']);
            }

            if (! isset($data['editorial_status']) && ! auth()->user()->canPublish()) {
                $data['editorial_status'] = 'draft';
            }

            if (! isset($data['editorial_status'])) {
                $data['editorial_status'] = array_key_exists('estado', $data)
                    ? ($data['estado'] == 1 ? 'published' : 'draft')
                    : 'draft';
            }

            if (($data['editorial_status'] ?? null) === 'published' && empty($data['published_at'])) {
                $data['published_at'] = now();
            }

            $event = Event::create($data);

            $this->syncInterpretes($event, $data);

            if ($image) {
                $this->imageService->process(
                    $image,
                    $event,
                    'event',
                    'events',
                    false,
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
            foreach (['body', 'detalles'] as $field) {
                if (array_key_exists($field, $data)) {
                    $data[$field] = RichTextHeadingSanitizer::normalize($data[$field]);
                }
            }

            if (isset($data['estado'])) {
                $data['editorial_status'] = $data['estado'] == 1 ? 'published' : 'draft';
                unset($data['estado']);
            }

            if (! isset($data['editorial_status']) && ! auth()->user()->canPublish()) {
                $data['editorial_status'] = 'draft';
            }

            if (! isset($data['editorial_status']) && isset($data['estado'])) {
                $data['editorial_status'] = $data['estado'] == 1 ? 'published' : 'draft';
            }

            if (isset($data['slug']) && empty($data['slug'])) {
                unset($data['slug']);
            }

            if (($data['editorial_status'] ?? null) === 'published' && empty($data['published_at']) && empty($event->published_at)) {
                $data['published_at'] = now();
            }

            $event->update($data);

            $this->syncInterpretes($event, $data);

            if ($image) {
                $this->imageService->process(
                    $image,
                    $event,
                    'event',
                    'events',
                    true,
                    $event->slug
                );
            }

            return $event;
        });
    }

    protected function syncInterpretes(Event $event, array $data): void
    {
        $interpreteIds = [];

        if (! empty($data['interprete_id'])) {
            $interpreteIds[] = $data['interprete_id'];
        }

        if (! empty($data['interprete_secundarios']) && is_array($data['interprete_secundarios'])) {
            $interpreteIds = array_merge($interpreteIds, $data['interprete_secundarios']);
        }

        if (! empty($interpreteIds)) {
            $event->interpretes()->sync(array_unique($interpreteIds));
        }
    }
}
