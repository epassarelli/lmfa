<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateNewsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'sometimes|string|max:255',
            'body' => 'sometimes|string',
            'slug' => 'sometimes|nullable|string|max:255',
            'subtitle' => 'sometimes|nullable|string|max:255',
            'excerpt' => 'sometimes|nullable|string|max:1000',
            'featured_image_path' => 'sometimes|nullable|string|max:255',
            'created_by' => 'sometimes|nullable|exists:users,id',
            'approved_by' => 'sometimes|nullable|exists:users,id',
            'published_at' => 'sometimes|nullable|date',
            'editorial_status' => ['sometimes', 'nullable', Rule::in(['draft', 'published', 'archived'])],
            'seo_title' => 'sometimes|nullable|string|max:255',
            'meta_description' => 'sometimes|nullable|string|max:320',
            'news_type' => 'sometimes|nullable|string|max:50',
            'publication_mode' => 'sometimes|nullable|string|max:40',

            'titulo' => 'sometimes|string|max:255',
            'noticia' => 'sometimes|string',
            'foto' => 'nullable',
            'user_id' => 'sometimes|nullable|exists:users,id',
            'publicar' => 'sometimes|nullable|date',

            'categoria_id' => 'sometimes|exists:categorias,id',
            'interprete_id' => 'sometimes|nullable|exists:interpretes,id',
            'interprete_principal_id' => 'sometimes|nullable|exists:interpretes,id',
            'interprete_secundarios' => 'sometimes|nullable|array',
            'interprete_secundarios.*' => 'exists:interpretes,id',
            'visitas' => 'sometimes|nullable|integer|min:0',
            'estado' => ['sometimes', 'nullable', Rule::in(['0', '1', 0, 1])],
        ];
    }
}
