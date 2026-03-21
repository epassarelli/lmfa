<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class StoreFestivalRequest extends FormRequest
{
  public function authorize(): bool
  {
    return true;
  }

  public function rules(): array
  {
    return [
      'titulo' => 'required|string|max:255',
      'detalle' => 'required|string',
      'foto' => 'nullable|string',
      'provincia_id' => 'required|exists:provincias,id',
      'mes_id' => 'required|exists:meses,id',
      'publicar' => 'required|date',
      'user_id' => 'required|exists:users,id',
      'estado' => 'nullable|string',
    ];
  }
}
