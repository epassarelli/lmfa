<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'required_without:show|string|max:255',
            'body' => 'nullable|string',
            'show' => 'required_without:title|string|max:255',
            'detalles' => 'nullable|string',
            'subtitle' => 'nullable|string|max:255',
            'excerpt' => 'nullable|string|max:1000',
            'slug' => 'nullable|string|max:255',
            'start_at' => 'required_without:fecha|date',
            'fecha' => 'required_without:start_at|date',
            'end_at' => 'nullable|date|after:start_at',
            'event_type' => 'nullable|string|max:50',
            'modality' => 'nullable|string|max:30',
            'timezone' => 'nullable|string|max:50',
            'province_id' => 'nullable|exists:provincias,id',
            'city' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'lugar' => 'nullable|string|max:255',
            'direccion' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'ticket_url' => 'nullable|url|max:255',
            'price_text' => 'nullable|string|max:255',
            'is_free' => 'nullable|boolean',
            'capacity' => 'nullable|integer|min:0',
            'status' => 'nullable|string|max:30',
            'editorial_status' => ['nullable', Rule::in(['draft', 'published', 'archived'])],
            'publication_mode' => 'nullable|string|max:40',
            'featured_image_path' => 'nullable|string|max:255',
            'seo_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:320',
            'published_at' => 'nullable|date',
            'created_by' => 'nullable|exists:users,id',
            'user_id' => 'nullable|exists:users,id',
            'interprete_id' => 'nullable|exists:interpretes,id',
            'interprete_secundarios' => 'nullable|array',
            'interprete_secundarios.*' => 'exists:interpretes,id',
            'estado' => ['nullable', Rule::in(['0', '1', 0, 1])],
            'publicar' => 'nullable|date',
        ];
    }
}
