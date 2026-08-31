<?php

namespace App\Http\Requests\Api;

use App\Http\Requests\Concerns\NormalizesRichTextFields;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateArtistRequest extends FormRequest
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

    public function rules(): array
    {
        return [
            'interprete' => 'sometimes|string|max:255',
            'artist_type' => 'sometimes|nullable|in:soloist,group',
            'slug' => ['sometimes', 'string', 'max:255', Rule::unique('interpretes', 'slug')->ignore($this->route('artist')?->id)],
            'biografia' => 'sometimes|string',
            'excerpt' => 'sometimes|nullable|string|max:1000',
            'seo_title' => 'sometimes|nullable|string|max:255',
            'meta_description' => 'sometimes|nullable|string|max:320',
            'image_alt' => 'sometimes|nullable|string|max:255',
            'foto' => 'nullable|string',
            'featured_image_path' => 'sometimes|nullable|string|max:2048',
            'featured_image_url' => 'sometimes|nullable|url|max:2048',
            'correo' => 'nullable|email|max:255',
            'telefono' => 'nullable|string|max:255',
            'facebook' => 'nullable|url',
            'instagram' => 'nullable|url',
            'twitter' => 'nullable|url',
            'youtube' => 'nullable|url',
            'web' => 'nullable|url',
            'estado' => 'nullable|boolean',
            'user_id' => 'sometimes|exists:users,id',
        ];
    }
}
