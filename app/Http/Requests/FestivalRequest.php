<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FestivalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'excerpt' => 'nullable|string|max:1000',
            'body' => 'required|string',
            'foto' => 'nullable|image|mimes:jpeg,png|max:200',
            'province_id' => 'required|exists:provincias,id',
            'locality_id' => 'nullable|exists:localities,id',
            'mes_id' => 'required|exists:meses,id',
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
