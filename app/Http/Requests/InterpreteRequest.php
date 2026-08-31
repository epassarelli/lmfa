<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\NormalizesRichTextFields;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class InterpreteRequest extends FormRequest
{
    use NormalizesRichTextFields;

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->normalizeRichTextFields(['biografia']);
    }

    public function rules()
    {
        $rules = [
            'interprete' => 'required|string|max:255',
            'artist_type' => 'nullable|in:soloist,group',
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('interpretes', 'slug')->ignore($this->route('interprete')?->id)],
            'biografia' => 'required|string',
            'excerpt' => 'nullable|string|max:1000',
            'seo_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:320',
            'image_alt' => 'nullable|string|max:255',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            'correo' => 'nullable|email|max:255',
            'telefono' => 'nullable|string|max:255',
            'facebook' => 'nullable|url',
            'instagram' => 'nullable|url',
            'twitter' => 'nullable|url',
            'web' => 'nullable|url',
            'estado' => 'nullable|boolean',
        ];

        if ($this->isMethod('post')) {
            $rules['foto'] = 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120';
        }

        return $rules;
    }
}
