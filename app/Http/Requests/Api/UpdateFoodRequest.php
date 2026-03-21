<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFoodRequest extends FormRequest
{
  public function authorize(): bool
  {
    return true;
  }

  public function rules(): array
  {
    return [
      'titulo' => 'sometimes|string|max:255',
      'receta' => 'sometimes|string',
      'foto' => 'nullable|string',
      'publicar' => 'sometimes|date',
      'estado' => 'nullable|string',
    ];
  }
}
