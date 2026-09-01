<?php

namespace App\Http\Requests\Api;

use App\Http\Requests\Concerns\NormalizesRichTextFields;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StoreArtistRequest extends FormRequest
{
    use NormalizesRichTextFields;

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->normalizeRichTextFields(['biografia']);

        if (blank($this->input('slug')) && filled($this->input('interprete'))) {
            $this->merge(['slug' => Str::slug((string) $this->input('interprete'))]);
        }
    }

    public function rules(): array
    {
        return [
            'interprete' => 'required|string|max:255',
            'artist_type' => 'nullable|in:soloist,group',
            'slug' => ['required', 'string', 'max:255', Rule::unique('interpretes', 'slug')],
            'biografia' => 'required|string',
            'excerpt' => 'nullable|string|max:1000',
            'seo_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:320',
            'image_alt' => 'nullable|string|max:255',
            'foto' => 'nullable|string',
            'featured_image_path' => 'nullable|string|max:2048',
            'featured_image_url' => 'nullable|url|max:2048',
            'correo' => 'nullable|email|max:255',
            'telefono' => 'nullable|string|max:255',
            'facebook' => 'nullable|url|max:255',
            'instagram' => 'nullable|url|max:255',
            'twitter' => 'nullable|url|max:255',
            'youtube' => 'nullable|url|max:255',
            'web' => 'nullable|url|max:255',
            'estado' => 'nullable|boolean',
            'user_id' => 'prohibited',
        ];
    }
}
