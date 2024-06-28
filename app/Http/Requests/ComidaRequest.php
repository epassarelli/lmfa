<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ComidaRequest extends FormRequest
{
  public function authorize()
  {
    return true;
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
