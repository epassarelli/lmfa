<?php

namespace App\Http\Requests\Api;

use App\Http\Requests\Concerns\NormalizesRichTextFields;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreFestivalRequest extends FormRequest
{
    use NormalizesRichTextFields;

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->normalizeRichTextFields(['body']);
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('festivales', 'slug')],
            'excerpt' => 'nullable|string|max:1000',
            'body' => 'required|string',
            'featured_image' => 'nullable|image|max:5120',
            'featured_image_url' => 'nullable|url|max:2048',
            'featured_image_path' => 'nullable|string|max:255',
            'province_id' => 'required|exists:provincias,id',
            'locality_id' => 'nullable|exists:localities,id',
            'mes_id' => 'required|exists:meses,id',
            'published_at' => 'nullable|date',
            'user_id' => 'required|exists:users,id',
            'status' => 'required|in:draft,published,archived',
            'image_alt' => 'nullable|string|max:255',
            'seo_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:320',
            'news_ids' => 'nullable|array',
            'news_ids.*' => 'integer|exists:news,id',
            'event_ids' => 'nullable|array',
            'event_ids.*' => 'integer|exists:events,id',
            'interprete_ids' => 'nullable|array',
            'interprete_ids.*' => 'integer|exists:interpretes,id',
            'knowledge_article_ids' => 'nullable|array',
            'knowledge_article_ids.*' => 'integer|exists:knowledge_articles,id',
        ];
    }
}
