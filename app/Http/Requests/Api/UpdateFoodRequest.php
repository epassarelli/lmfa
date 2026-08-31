<?php

namespace App\Http\Requests\Api;

use App\Http\Requests\Concerns\NormalizesRichTextFields;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateFoodRequest extends FormRequest
{
    use NormalizesRichTextFields;

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->normalizeRichTextFields(['receta']);
    }

    public function rules(): array
    {
        return [
            'titulo' => 'sometimes|string|max:255',
            'slug' => ['sometimes', 'string', 'max:255', Rule::unique('comidas', 'slug')->ignore($this->route('food')?->id)],
            'receta' => 'sometimes|string',
            'excerpt' => 'sometimes|nullable|string|max:1000',
            'ingredients' => 'sometimes|nullable|array',
            'ingredients.*' => 'string|max:500',
            'instructions' => 'sometimes|nullable|array',
            'instructions.*' => 'string|max:2000',
            'prep_time_minutes' => 'sometimes|nullable|integer|min:0|max:1440',
            'cook_time_minutes' => 'sometimes|nullable|integer|min:0|max:1440',
            'servings' => 'sometimes|nullable|string|max:100',
            'region' => 'sometimes|nullable|string|max:150',
            'seo_title' => 'sometimes|nullable|string|max:255',
            'meta_description' => 'sometimes|nullable|string|max:320',
            'image_alt' => 'sometimes|nullable|string|max:255',
            'foto' => 'nullable|string',
            'featured_image_path' => 'sometimes|nullable|string|max:2048',
            'featured_image_url' => 'sometimes|nullable|url|max:2048',
            'publicar' => 'sometimes|nullable|date',
            'visitas' => 'nullable|integer|min:0',
            'estado' => 'sometimes|integer|in:0,1',
            'user_id' => 'sometimes|exists:users,id',
        ];
    }
}
