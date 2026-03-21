<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFestivalRequest extends FormRequest
{
  public function authorize(): bool
  {
    return true;
  }

  public function rules(): array
  {
    return [
      'titulo' => 'sometimes|string|max:255',
      'detalle' => 'sometimes|string',
      'foto' => 'nullable|string',
      'provincia_id' => 'sometimes|exists:provincias,id',
      'mes_id' => 'sometimes|exists:meses,id',
      'publicar' => 'sometimes|date',
      'user_id' => 'sometimes|exists:users,id',
      'estado' => 'nullable|string',
    ];
  }
}
