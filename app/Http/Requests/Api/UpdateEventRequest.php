<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'sometimes|string|max:255',
            'body' => 'sometimes|nullable|string',
            'show' => 'sometimes|string|max:255',
            'detalles' => 'sometimes|nullable|string',
            'subtitle' => 'sometimes|nullable|string|max:255',
            'excerpt' => 'sometimes|nullable|string|max:1000',
            'slug' => 'sometimes|nullable|string|max:255',
            'start_at' => 'sometimes|date',
            'fecha' => 'sometimes|date',
            'end_at' => 'sometimes|nullable|date',
            'event_type' => 'sometimes|nullable|string|max:50',
            'modality' => 'sometimes|nullable|string|max:30',
            'timezone' => 'sometimes|nullable|string|max:50',
            'province_id' => 'sometimes|nullable|exists:provincias,id',
            'city' => 'sometimes|nullable|string|max:255',
            'address' => 'sometimes|nullable|string|max:255',
            'lugar' => 'sometimes|nullable|string|max:255',
            'direccion' => 'sometimes|nullable|string|max:255',
            'latitude' => 'sometimes|nullable|numeric',
            'longitude' => 'sometimes|nullable|numeric',
            'ticket_url' => 'sometimes|nullable|url|max:255',
            'price_text' => 'sometimes|nullable|string|max:255',
            'is_free' => 'sometimes|nullable|boolean',
            'capacity' => 'sometimes|nullable|integer|min:0',
            'status' => 'sometimes|nullable|string|max:30',
            'editorial_status' => ['sometimes', 'nullable', Rule::in(['draft', 'published', 'archived'])],
            'publication_mode' => 'sometimes|nullable|string|max:40',
            'featured_image_path' => 'sometimes|nullable|string|max:255',
            'seo_title' => 'sometimes|nullable|string|max:255',
            'meta_description' => 'sometimes|nullable|string|max:320',
            'published_at' => 'sometimes|nullable|date',
            'created_by' => 'sometimes|nullable|exists:users,id',
            'user_id' => 'sometimes|nullable|exists:users,id',
            'interprete_id' => 'sometimes|nullable|exists:interpretes,id',
            'interprete_secundarios' => 'sometimes|nullable|array',
            'interprete_secundarios.*' => 'exists:interpretes,id',
            'estado' => ['sometimes', 'nullable', Rule::in(['0', '1', 0, 1])],
            'publicar' => 'sometimes|nullable|date',
        ];
    }
}
