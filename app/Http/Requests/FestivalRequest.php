<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\NormalizesRichTextFields;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class FestivalRequest extends FormRequest
{
    use NormalizesRichTextFields;

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->normalizeRichTextFields(['body']);

        if (blank($this->input('slug')) && filled($this->input('title'))) {
            $this->merge(['slug' => Str::slug((string) $this->input('title'))]);
        }
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('festivales', 'slug')->ignore($this->route('festival')?->id)],
            'excerpt' => 'nullable|string|max:1000',
            'body' => 'required|string',
            'foto' => 'nullable|image|mimes:jpeg,png,webp|max:5120',
            'province_id' => 'required|exists:provincias,id',
            'locality_id' => 'nullable|exists:localities,id',
            'mes_id' => 'required|exists:meses,id',
            'image_alt' => 'nullable|string|max:255',
            'seo_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:320',
            'status' => 'required|in:draft,published,archived',
            'published_at' => 'nullable|date',
            'news_ids' => 'nullable|array',
            'news_ids.*' => 'exists:news,id',
            'event_ids' => 'nullable|array',
            'event_ids.*' => 'exists:events,id',
            'interprete_ids' => 'nullable|array',
            'interprete_ids.*' => 'exists:interpretes,id',
            'knowledge_article_ids' => 'nullable|array',
            'knowledge_article_ids.*' => 'exists:knowledge_articles,id',
        ];
    }
}
