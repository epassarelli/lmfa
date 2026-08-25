<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\NormalizesRichTextFields;
use Illuminate\Foundation\Http\FormRequest;

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
            'receta' => 'required|string',
            'foto' => 'nullable|image|mimes:jpeg,png|max:200',
            'publicar' => 'required|date',
        ];
    }
}
