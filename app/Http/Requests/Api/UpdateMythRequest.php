<?php

namespace App\Http\Requests\Api;

use App\Http\Requests\Concerns\NormalizesRichTextFields;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMythRequest extends FormRequest
{
    use NormalizesRichTextFields;

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->normalizeRichTextFields(['mito']);
    }

    public function rules(): array
    {
        return [
            'titulo' => 'sometimes|string|max:255',
            'content_type' => 'sometimes|nullable|in:myth,legend,urban_legend',
            'slug' => ['sometimes', 'string', 'max:255', Rule::unique('mitos', 'slug')->ignore($this->route('myth')?->id)],
            'mito' => 'sometimes|string',
            'excerpt' => 'sometimes|nullable|string|max:1000',
            'region' => 'sometimes|nullable|string|max:150',
            'seo_title' => 'sometimes|nullable|string|max:255',
            'meta_description' => 'sometimes|nullable|string|max:320',
            'image_alt' => 'sometimes|nullable|string|max:255',
            'foto' => 'nullable|string',
            'featured_image_path' => 'sometimes|nullable|string|max:2048',
            'featured_image_url' => 'sometimes|nullable|url|max:2048',
            'publicar' => 'sometimes|nullable|date',
            'visitas' => 'prohibited',
            'estado' => 'sometimes|integer|in:0,1',
            'user_id' => 'prohibited',
        ];
    }
}
