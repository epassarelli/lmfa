<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class UpdateArtistRequest extends FormRequest
{
  public function authorize(): bool
  {
    return true;
  }

  public function rules(): array
  {
    return [
      'interprete' => 'sometimes|string|max:255',
      'biografia' => 'sometimes|string',
      'foto' => 'nullable|string',
      'correo' => 'nullable|email|max:255',
      'telefono' => 'nullable|string|max:255',
      'facebook' => 'nullable|url',
      'instagram' => 'nullable|url',
      'twitter' => 'nullable|url',
      'web' => 'nullable|url',
      'estado' => 'nullable|boolean',
      'user_id' => 'sometimes|exists:users,id',
    ];
  }
}
