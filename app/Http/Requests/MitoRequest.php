<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MitoRequest extends FormRequest
{
  public function authorize()
  {
    return true;
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
