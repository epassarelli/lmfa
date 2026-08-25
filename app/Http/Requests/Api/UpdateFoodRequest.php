<?php

namespace App\Http\Requests\Api;

use App\Http\Requests\Concerns\NormalizesRichTextFields;
use Illuminate\Foundation\Http\FormRequest;

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
            'receta' => 'sometimes|string',
            'foto' => 'nullable|string',
            'publicar' => 'sometimes|date',
            'visitas' => 'nullable|integer|min:0',
            'estado' => 'sometimes|integer',
        ];
    }
}
