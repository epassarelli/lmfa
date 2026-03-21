<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class StoreFoodRequest extends FormRequest
{
  public function authorize(): bool
  {
    return true;
  }

  public function rules(): array
  {
    return [
      'titulo' => 'required|string|max:255',
      'receta' => 'required|string',
      'foto' => 'nullable|string',
      'publicar' => 'required|date',
      'estado' => 'nullable|string',
    ];
  }
}
