<?php

namespace App\Http\Requests\Api;

use App\Http\Requests\Concerns\NormalizesRichTextFields;
use Illuminate\Foundation\Http\FormRequest;

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
    }

    public function rules(): array
    {
        return [
            'interprete' => 'required|string|max:255',
            'biografia' => 'required|string',
            'foto' => 'nullable|string',
            'correo' => 'nullable|email|max:255',
            'telefono' => 'nullable|string|max:255',
            'facebook' => 'nullable|url',
            'instagram' => 'nullable|url',
            'twitter' => 'nullable|url',
            'web' => 'nullable|url',
            'estado' => 'nullable|boolean',
            'user_id' => 'required|exists:users,id',
        ];
    }
}
