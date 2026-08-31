<?php

namespace App\Http\Requests\Api;

use App\Http\Requests\Api\Concerns\ResolvesKnowledgeCategoryInput;
use App\Support\RichTextHeadingSanitizer;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreKnowledgeArticleRequest extends FormRequest
{
    use ResolvesKnowledgeCategoryInput {
        prepareForValidation as prepareKnowledgeCategoryForValidation;
    }

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->prepareKnowledgeCategoryForValidation();

        if ($this->has('body')) {
            $this->merge([
                'body' => RichTextHeadingSanitizer::normalize($this->input('body')),
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'knowledge_category_id' => 'nullable|integer',
            'knowledge_category_slug' => 'nullable|string|max:255',
            'knowledge_category_name' => 'nullable|string|max:255',
            'title' => 'required|string|max:255',
            'slug' => [
                'required',
                'string',
                'max:255',
                Rule::unique('knowledge_articles', 'slug')->where(
                    fn ($query) => $query->where('knowledge_category_id', (int) $this->input('knowledge_category_id'))
                ),
            ],
            'excerpt' => 'nullable|string|max:1000',
            'body' => 'required|string',
            'image' => 'nullable|image',
            'featured_image_url' => 'nullable|url|max:2048',
            'featured_image_path' => 'nullable|string',
            'image_alt' => 'nullable|string|max:255',
            'seo_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:320',
            'primary_keyword' => 'nullable|string|max:255',
            'secondary_keywords' => 'nullable|string|max:1000',
            'editorial_status' => 'nullable|in:draft,published,archived',
            'published_at' => 'nullable|date',
            'last_verified_at' => 'nullable|date',
            'author_id' => 'nullable|exists:users,id',
            'reviewed_by' => 'nullable|exists:users,id',
            'interprete_ids' => 'nullable|array',
            'interprete_ids.*' => 'exists:interpretes,id',
            'cancion_ids' => 'nullable|array',
            'cancion_ids.*' => 'exists:canciones,id',
            'album_ids' => 'nullable|array',
            'album_ids.*' => 'exists:albunes,id',
            'festival_ids' => 'nullable|array',
            'festival_ids.*' => 'exists:festivales,id',
            'event_ids' => 'nullable|array',
            'event_ids.*' => 'exists:events,id',
            'provincia_ids' => 'nullable|array',
            'provincia_ids.*' => 'exists:provincias,id',
            'related_article_ids' => 'nullable|array',
            'related_article_ids.*' => 'exists:knowledge_articles,id',
        ];
    }
}
