<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\NormalizesRichTextFields;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ComidaRequest extends FormRequest
{
    use NormalizesRichTextFields;

    public function authorize()
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->normalizeRichTextFields(['receta']);
    }

    public function rules()
    {
        return [
            'titulo' => 'required|string|max:255',
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('comidas', 'slug')->ignore($this->route('comida')?->id)],
            'receta' => 'required|string',
            'excerpt' => 'nullable|string|max:1000',
            'ingredients' => 'nullable|array',
            'ingredients.*' => 'string|max:500',
            'instructions' => 'nullable|array',
            'instructions.*' => 'string|max:2000',
            'prep_time_minutes' => 'nullable|integer|min:0|max:1440',
            'cook_time_minutes' => 'nullable|integer|min:0|max:1440',
            'servings' => 'nullable|string|max:100',
            'region' => 'nullable|string|max:150',
            'seo_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:320',
            'image_alt' => 'nullable|string|max:255',
            'foto' => 'nullable|image|mimes:jpeg,png,webp|max:5120',
            'publicar' => 'nullable|date',
        ];
    }
}
