<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FestivalRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'titulo' => 'required|string|max:255',
            'detalle' => 'required|string',
            'foto' => 'nullable|image|mimes:jpeg,png|max:200',
            'provincia_id' => 'required|exists:provincias,id',
            'mes_id' => 'required|exists:meses,id',
            'publicar' => 'required|date',
        ];
    }
}
