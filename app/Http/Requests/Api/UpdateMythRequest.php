<?php

namespace App\Http\Requests\Api;

use App\Http\Requests\Concerns\NormalizesRichTextFields;
use Illuminate\Foundation\Http\FormRequest;

class UpdateMythRequest extends FormRequest
{
    use NormalizesRichTextFields;

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->normalizeRichTextFields(['mito']);
    }

    public function rules(): array
    {
        return [
            'titulo' => 'sometimes|string|max:255',
            'mito' => 'sometimes|string',
            'foto' => 'nullable|string',
            'publicar' => 'sometimes|date',
            'visitas' => 'nullable|integer|min:0',
            'estado' => 'sometimes|integer',
        ];
    }
}
