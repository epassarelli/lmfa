<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ShowRequest extends FormRequest
{
  public function authorize()
  {
    return true;
  }

  public function rules()
  {
    return [
      'show' => 'required|string|max:255',
      'detalle' => 'required|string',
      'foto' => 'nullable|image|mimes:jpeg,png|max:200',
      'fecha' => 'required|date',
      'hora' => 'required|string|max:255',
      'lugar' => 'required|string|max:255',
      'direccion' => 'required|string|max:255',
      'interprete_id' => 'required|exists:interpretes,id',
      'publicar' => 'required|date',
    ];
  }
}
