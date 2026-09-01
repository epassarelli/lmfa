<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\NormalizesRichTextFields;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MitoRequest extends FormRequest
{
    use NormalizesRichTextFields;

    public function authorize()
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->normalizeRichTextFields(['mito']);
    }

    public function rules()
    {
        return [
            'titulo' => 'required|string|max:255',
            'content_type' => 'nullable|in:myth,legend,urban_legend',
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('mitos', 'slug')->ignore($this->route('mito')?->id)],
            'mito' => 'required|string',
            'excerpt' => 'nullable|string|max:1000',
            'region' => 'nullable|string|max:150',
            'seo_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:320',
            'image_alt' => 'nullable|string|max:255',
            'foto' => 'nullable|image|mimes:jpeg,png,webp|max:5120',
            'publicar' => 'nullable|date',
            'estado' => 'nullable|integer|in:0,1',
        ];
    }
}
