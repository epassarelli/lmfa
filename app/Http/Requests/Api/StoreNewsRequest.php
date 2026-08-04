<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreNewsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'required_without:titulo|string|max:255',
            'body' => 'required_without:noticia|string',
            'slug' => 'nullable|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'excerpt' => 'nullable|string|max:1000',
            'featured_image_path' => 'nullable|string|max:255',
            'created_by' => 'nullable|exists:users,id',
            'approved_by' => 'nullable|exists:users,id',
            'published_at' => 'nullable|date',
            'editorial_status' => ['nullable', Rule::in(['draft', 'published', 'archived'])],
            'seo_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:320',
            'news_type' => 'nullable|string|max:50',
            'publication_mode' => 'nullable|string|max:40',

            'titulo' => 'required_without:title|string|max:255',
            'noticia' => 'required_without:body|string',
            'foto' => 'nullable',
            'user_id' => 'nullable|exists:users,id',
            'publicar' => 'nullable|date',

            'categoria_id' => 'required|exists:categorias,id',
            'interprete_id' => 'nullable|exists:interpretes,id',
            'interprete_principal_id' => 'nullable|exists:interpretes,id',
            'interprete_secundarios' => 'nullable|array',
            'interprete_secundarios.*' => 'exists:interpretes,id',
            'visitas' => 'nullable|integer|min:0',
            'estado' => ['nullable', Rule::in(['0', '1', 0, 1])],
        ];
    }
}
