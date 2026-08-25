<?php

namespace App\Http\Requests\Api;

use App\Http\Requests\Concerns\NormalizesRichTextFields;
use Illuminate\Foundation\Http\FormRequest;

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
    }

    public function rules(): array
    {
        return [
            'titulo' => 'required|string|max:255',
            'receta' => 'required|string',
            'foto' => 'nullable|string',
            'publicar' => 'required|date',
            'visitas' => 'nullable|integer|min:0',
            'estado' => 'required|integer',
        ];
    }
}
