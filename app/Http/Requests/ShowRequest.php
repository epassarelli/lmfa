<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\NormalizesRichTextFields;
use Illuminate\Foundation\Http\FormRequest;

class ShowRequest extends FormRequest
{
    use NormalizesRichTextFields;

    public function authorize()
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->normalizeRichTextFields(['body']);

        // El formulario envía un hidden vacío por relación para permitir desvincular todo.
        foreach (['festival_ids', 'knowledge_article_ids', 'penia_profile_ids'] as $field) {
            if (is_array($this->input($field))) {
                $this->merge([$field => array_values(array_filter($this->input($field), 'filled'))]);
            }
        }
    }

    public function rules(): array
    {
        return [
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
        ];
    }
}
