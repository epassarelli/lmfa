<?php

namespace App\Support;

use App\Models\Event;
use App\Models\EventProgram;
use App\Services\VenueService;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class EventInput
{
    public static function rules(): array
    {
        return array_merge(VenueService::inputRules(), [
            'program_id' => 'nullable|integer|exists:event_programs,id',
            'inherit_fields' => 'nullable|array',
            'inherit_fields.*' => 'in:body,excerpt,venue_id,ticket_url,price_text,is_free',
        ]);
    }

    public static function validate(array $data, ?Event $event = null): array
    {
        foreach (['show' => 'title', 'titulo' => 'title', 'detalles' => 'body', 'detalle' => 'body', 'fecha' => 'start_at', 'lugar' => 'city', 'direccion' => 'address', 'publicar' => 'published_at', 'user_id' => 'created_by'] as $legacy => $canonical) {
            if (array_key_exists($legacy, $data) && ! array_key_exists($canonical, $data)) {
                $data[$canonical] = $data[$legacy];
            }
            unset($data[$legacy]);
        }
        $data['title'] = $data['title'] ?? $event?->title;
        $data['start_at'] = $data['start_at'] ?? $event?->start_at;
        $rules = array_merge(self::rules(), [
            'title' => 'required|string|max:255', 'start_at' => 'required|date',
            'slug' => ['nullable', 'string', 'max:255', \Illuminate\Validation\Rule::unique('events', 'slug')->ignore($event?->id)],
            'end_at' => 'nullable|date|after_or_equal:start_at', 'body' => 'nullable|string',
            'interprete_ids' => 'nullable|array', 'interprete_ids.*' => 'integer|exists:interpretes,id',
            'interprete_id' => 'nullable|integer|exists:interpretes,id',
            'interprete_secundarios' => 'nullable|array', 'interprete_secundarios.*' => 'integer|exists:interpretes,id',
            'festival_ids' => 'nullable|array', 'festival_ids.*' => 'integer|exists:festivales,id',
            'knowledge_article_ids' => 'nullable|array', 'knowledge_article_ids.*' => 'integer|exists:knowledge_articles,id',
            'penia_profile_ids' => 'nullable|array', 'penia_profile_ids.*' => 'integer|exists:penia_profiles,id',
            'ticket_url' => 'nullable|url|max:255', 'price_text' => 'nullable|string|max:100',
            'excerpt' => 'nullable|string|max:1000', 'is_free' => 'nullable|boolean',
            'city' => 'nullable|string|max:255', 'address' => 'nullable|string|max:255',
            'province_id' => 'nullable|exists:provincias,id',
            'latitude' => 'nullable|numeric|between:-90,90', 'longitude' => 'nullable|numeric|between:-180,180',
            'published_at' => 'nullable|date', 'editorial_status' => 'nullable|in:draft,published,archived',
            'estado' => 'nullable|in:0,1', 'status' => 'nullable|in:active,cancelled,postponed,inactive',
        ]);
        $validationData = $data;
        if ($event && ! array_key_exists('end_at', $data)) {
            $validationData['end_at'] = $event->end_at;
        }
        Validator::make($validationData, $rules)->validate();
        $programId = array_key_exists('program_id', $data) ? $data['program_id'] : $event?->program_id;
        if ($programId) {
            $program = EventProgram::findOrFail($programId);
            if (! auth()->user()?->hasRole('administrador') && (int) $program->created_by !== (int) auth()->id()) {
                throw ValidationException::withMessages(['program_id' => 'No podés asociar jornadas a un conjunto de otro editor.']);
            }
        }
        if (! auth()->user()?->canPublish()) {
            $data['estado'] = 0;
            $data['editorial_status'] = 'draft';
            $data['published_at'] = null;
            unset($data['approved_by'], $data['approved_at']);
            if (array_key_exists('created_by', $data)) {
                $data['created_by'] = $event?->created_by ?? auth()->id();
            }
        }
        // Estas claves son internas: nunca aceptar control de herencia desde un payload.
        unset($data['program_overrides']);

        return $data;
    }
}
