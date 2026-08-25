<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\NormalizesKnowledgeArticleBody;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class KnowledgeArticleRequest extends FormRequest
{
    use NormalizesKnowledgeArticleBody;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $article = $this->route('knowledge_article');
        $articleId = $article?->id;
        $categoryId = (int) ($this->input('knowledge_category_id') ?: $article?->knowledge_category_id);

        return [
            'knowledge_category_id' => 'required|exists:knowledge_categories,id',
            'title' => 'required|string|max:255',
            'slug' => [
                'required',
                'string',
                'max:255',
                Rule::unique('knowledge_articles', 'slug')
                    ->where(fn ($query) => $query->where('knowledge_category_id', $categoryId))
                    ->ignore($articleId),
            ],
            'excerpt' => 'nullable|string|max:1000',
            'body' => 'required|string',
            'image' => $article ? 'nullable|image' : 'nullable|image',
            'image_alt' => 'nullable|string|max:255',
            'seo_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:320',
            'primary_keyword' => 'nullable|string|max:255',
            'secondary_keywords' => 'nullable|string|max:1000',
            'editorial_status' => 'required|in:draft,published,archived',
            'published_at' => 'nullable|date',
            'last_verified_at' => 'nullable|date',
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
