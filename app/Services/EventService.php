<?php

namespace App\Services;

use App\Models\Event;
use App\Models\EventProgram;
use App\Support\EventInput;
use App\Support\ImageSourceMetadata;
use App\Support\RichTextHeadingSanitizer;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class EventService
{
    public function __construct(
        protected ImageUploadService $imageService,
        protected ImageSourceResolver $imageResolver
    ) {
    }

    /**
     * Crea un nuevo evento centralizando toda la logica de negocio.
     */
    public function createEvent(array $data, mixed $image = null): Event
    {
        return DB::transaction(function () use ($data, $image) {
            $data = EventInput::validate($data);
            $data = $this->programDefaults($data);
            app(VenueService::class)->resolve($data);
            $data['created_by'] = $data['created_by'] ?? auth()->id();
            $data['is_free'] = $data['is_free'] ?? false;

            foreach (['body', 'detalles'] as $field) {
                if (array_key_exists($field, $data)) {
                    $data[$field] = RichTextHeadingSanitizer::normalize($data[$field]);
                }
            }

            if (empty($data['slug'])) {
                $data['slug'] = Str::slug($data['title']).'-'.Str::lower(Str::random(10));
            }

            if (isset($data['estado'])) {
                $data['editorial_status'] = $data['estado'] == 1 ? 'published' : 'draft';
                unset($data['estado']);
            }

            if (! isset($data['editorial_status']) && ! auth()->user()->canPublish()) {
                $data['editorial_status'] = 'draft';
            }

            // Quien no puede publicar nunca publica, aunque el request traiga estado=1: va a revisión.
            if (($data['editorial_status'] ?? null) === 'published' && auth()->user() && ! auth()->user()->canPublish()) {
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

            $this->syncEditorialRelations($event, $data);

            $this->processImage($event, $image, false);

            return $event;
        });
    }

    /**
     * Actualiza un evento existente.
     */
    public function updateEvent(Event $event, array $data, mixed $image = null): Event
    {
        return DB::transaction(function () use ($event, $data, $image) {
            $data = EventInput::validate($data, $event);
            $data = $this->programDefaults($data, $event);
            app(VenueService::class)->resolve($data, $event->venue_id);
            if (array_key_exists('is_free', $data) && $data['is_free'] === null) {
                $data['is_free'] = false;
            }

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

            // Quien no puede publicar nunca publica, aunque el request traiga estado=1: va a revisión.
            if (($data['editorial_status'] ?? null) === 'published' && auth()->user() && ! auth()->user()->canPublish()) {
                $data['editorial_status'] = 'draft';
            }

            if (isset($data['slug']) && empty($data['slug'])) {
                unset($data['slug']);
            }

            if (($data['editorial_status'] ?? null) === 'published' && empty($data['published_at']) && empty($event->published_at)) {
                $data['published_at'] = now();
            }

            $event->update($data);

            $this->syncInterpretes($event, $data);

            $this->syncEditorialRelations($event, $data);

            $this->processImage($event, $image, true);

            return $event;
        });
    }

    protected function processImage(Event $event, mixed $image, bool $replace): void
    {
        if (! $image) {
            return;
        }

        $resolved = null;

        try {
            $resolved = $this->imageResolver->resolve($image);

            if (! $resolved instanceof UploadedFile) {
                return;
            }

            $media = $this->imageService->process(
                $resolved,
                $event,
                'event',
                'events',
                $replace,
                $event->slug,
                ImageSourceMetadata::from($image)
            );

            $event->forceFill([
                'featured_image_id' => $media->id,
                'featured_image_path' => $media->path,
            ])->save();
        } finally {
            $this->imageResolver->cleanupTemporary($resolved);
        }
    }

    private function programDefaults(array $data, ?Event $event = null): array
    {
        $id = array_key_exists('program_id', $data) ? $data['program_id'] : $event?->program_id;
        if (! $id) {
            if (array_key_exists('program_id', $data)) {
                $data['program_overrides'] = null;
            }
            unset($data['inherit_fields']);

            return $data;
        }
        $program = EventProgram::findOrFail($id);
        $sameProgram = $event && (int) $event->program_id === (int) $id;
        $overrides = $sameProgram ? ($event->program_overrides ?? []) : [];
        foreach (EventProgramService::COMMON_FIELDS as $field) {
            if (in_array($field, $data['inherit_fields'] ?? [], true)) {
                $data[$field] = $program->{$field};
                $overrides = array_diff($overrides, [$field]);
            } elseif (array_key_exists($field, $data)) {
                $matches = $field === 'is_free' ? (bool) $data[$field] === (bool) $program->{$field} : (string) $data[$field] === (string) $program->{$field};
                if (! $matches) {
                    $overrides[] = $field;
                } else {
                    $overrides = array_diff($overrides, [$field]);
                }
            } elseif (! $sameProgram) {
                $data[$field] = $program->{$field};
            }
        }
        $data['program_overrides'] = array_values(array_unique($overrides));
        unset($data['inherit_fields']);

        return $data;
    }

    /**
     * Sincroniza los pivots existentes solo si el payload trae la clave: la API y los
     * formularios que no exponen estas relaciones no las tocan.
     */
    protected function syncEditorialRelations(Event $event, array $data): void
    {
        foreach ([
            'festival_ids' => 'festivales',
            'knowledge_article_ids' => 'knowledgeArticles',
            'penia_profile_ids' => 'peniaProfiles',
        ] as $key => $relation) {
            if (array_key_exists($key, $data)) {
                $event->{$relation}()->sync(array_values(array_unique(array_map('intval', array_filter($data[$key] ?? [])))));
            }
        }
    }

    protected function syncInterpretes(Event $event, array $data): void
    {
        if (array_key_exists('interprete_ids', $data)) {
            $event->interpretes()->sync(array_values(array_unique(array_filter($data['interprete_ids'] ?? []))));

            return;
        }

        $interpreteIds = [];

        if (! empty($data['interprete_id'])) {
            $interpreteIds[] = $data['interprete_id'];
        }

        if (! empty($data['interprete_secundarios']) && is_array($data['interprete_secundarios'])) {
            $interpreteIds = array_merge($interpreteIds, $data['interprete_secundarios']);
        }

        if (array_key_exists('interprete_id', $data) || array_key_exists('interprete_secundarios', $data)) {
            $event->interpretes()->sync(array_unique($interpreteIds));
        }
    }
}
