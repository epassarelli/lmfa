<?php

namespace App\Http\Requests\Api;

use App\Http\Requests\Concerns\NormalizesRichTextFields;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StoreFoodRequest extends FormRequest
{
    use NormalizesRichTextFields;

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->normalizeRichTextFields(['receta']);

        if (blank($this->input('slug')) && filled($this->input('titulo'))) {
            $this->merge(['slug' => Str::slug((string) $this->input('titulo'))]);
        }
    }

    public function rules(): array
    {
        return [
            'titulo' => 'required|string|max:255',
            'slug' => ['required', 'string', 'max:255', Rule::unique('comidas', 'slug')],
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
            'foto' => 'nullable|string',
            'featured_image_path' => 'nullable|string|max:2048',
            'featured_image_url' => 'nullable|url|max:2048',
            'publicar' => 'nullable|date',
            'visitas' => 'prohibited',
            'estado' => 'nullable|integer|in:0,1',
            'user_id' => 'prohibited',
        ];
    }
}
