<?php

namespace App\Http\Requests\Api;

use App\Http\Requests\Concerns\NormalizesRichTextFields;
use Illuminate\Foundation\Http\FormRequest;

class UpdateFestivalRequest extends FormRequest
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
            'title' => 'sometimes|string|max:255',
            'slug' => 'sometimes|string|max:255',
            'excerpt' => 'nullable|string|max:1000',
            'body' => 'sometimes|string',
            'featured_image' => 'sometimes|nullable|image|max:5120',
            'featured_image_url' => 'sometimes|nullable|url|max:2048',
            'featured_image_path' => 'nullable|string|max:255',
            'province_id' => 'sometimes|exists:provincias,id',
            'locality_id' => 'nullable|exists:localities,id',
            'mes_id' => 'sometimes|exists:meses,id',
            'published_at' => 'sometimes|date',
            'user_id' => 'sometimes|exists:users,id',
            'status' => 'sometimes|in:draft,published,archived',
            'seo_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:320',
        ];
    }
}
