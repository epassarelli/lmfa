<?php

namespace App\Http\Requests\Api;

use App\Http\Requests\Concerns\NormalizesRichTextFields;
use Illuminate\Foundation\Http\FormRequest;

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
            'slug' => 'nullable|string|max:255',
            'excerpt' => 'nullable|string|max:1000',
            'body' => 'required|string',
            'featured_image_path' => 'nullable|string|max:255',
            'province_id' => 'required|exists:provincias,id',
            'locality_id' => 'nullable|exists:localities,id',
            'mes_id' => 'required|exists:meses,id',
            'published_at' => 'nullable|date',
            'user_id' => 'required|exists:users,id',
            'status' => 'required|in:draft,published,archived',
            'seo_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:320',
        ];
    }
}
