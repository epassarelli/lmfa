<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\NormalizesRichTextFields;
use App\Http\Requests\Concerns\ValidatesOwnArtistOnMembershipAccess;
use Illuminate\Foundation\Http\FormRequest;

class ShowRequest extends FormRequest
{
    use NormalizesRichTextFields;
    use ValidatesOwnArtistOnMembershipAccess;

    public function authorize()
    {
        return true;
    }

    public function withValidator($validator): void
    {
        $user = $this->user();
        $event = $this->route('event');

        $membershipOnly = $user && ! $user->hasRole('administrador') && ($user->isRestrictedByEntities() || ($event
            ? (int) $event->created_by !== (int) $user->id
            : ! $user->hasRole('colaborador')));

        // En un evento de varias jornadas los artistas viven dentro de cada jornada.
        $sessionArtists = collect((array) $this->input('sessions', []))->flatMap(fn ($session) => (array) ($session['interprete_ids'] ?? []))->all();

        $this->requireOwnArtistOrFestival(
            $validator,
            (bool) $membershipOnly,
            array_merge((array) $this->input('interprete_ids', []), $sessionArtists, [$this->input('interprete_id')]),
            (array) $this->input('festival_ids', []),
            'interprete_ids'
        );
    }

    protected function prepareForValidation(): void
    {
        $this->normalizeRichTextFields(['body']);

        // El formulario envía un hidden vacío por relación para permitir desvincular todo.
        foreach (['interprete_ids', 'festival_ids', 'knowledge_article_ids', 'penia_profile_ids'] as $field) {
            if (is_array($this->input($field))) {
                $this->merge([$field => array_values(array_filter($this->input($field), 'filled'))]);
            }
        }
    }

    public function rules(): array
    {
        if (! $this->route('event') && $this->input('event_format') === 'multiple') {
            $rules = \App\Services\EventProgramService::rules();
            $rules['sessions'] = 'required|array|min:2|max:60';

            return array_merge($rules, [
                'event_format' => 'required|in:single,multiple',
                'estado' => 'nullable|integer|in:0,1',
                'imagen_destacada' => 'nullable|image|max:5120',
            ]);
        }

        return array_merge(\App\Support\EventInput::rules(), [
            'event_format' => 'nullable|in:single,multiple',
            'title' => 'required|string|max:255',
            'body' => 'required|string',
            'start_at' => 'required|date',
            'end_at' => 'nullable|date|after_or_equal:start_at',
            'excerpt' => 'nullable|string|max:500',
            'festival_ids' => 'nullable|array',
            'festival_ids.*' => 'integer|exists:festivales,id',
            'knowledge_article_ids' => 'nullable|array',
            'knowledge_article_ids.*' => 'integer|exists:knowledge_articles,id',
            'penia_profile_ids' => 'nullable|array',
            'penia_profile_ids.*' => 'integer|exists:penia_profiles,id',
            'published_at' => 'nullable|date',
            'city' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'province_id' => 'nullable|exists:provincias,id',
            'interprete_id' => 'nullable|exists:interpretes,id',
            'interprete_ids' => 'nullable|array',
            'interprete_ids.*' => 'exists:interpretes,id',
            'ticket_url' => 'nullable|url|max:255',
            'price_text' => 'nullable|string|max:100',
            'is_free' => 'nullable|boolean',
            'slug' => 'nullable|string|max:255|unique:events,slug,'.($this->route('event')?->id ?? $this->route('show')?->id ?? ''),
            'estado' => 'nullable|integer|in:0,1',
            'foto' => 'nullable|image|max:5120',
            'imagen_destacada' => 'nullable|image|max:5120',
        ]);
    }
}
