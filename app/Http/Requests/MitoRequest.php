<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\NormalizesRichTextFields;
use Illuminate\Foundation\Http\FormRequest;

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
            'mito' => 'required|string',
            'foto' => 'nullable|image|mimes:jpeg,png|max:200',
            'publicar' => 'required|date',
        ];
    }
}
