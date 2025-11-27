<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSongRequest extends FormRequest
{
  public function authorize(): bool
  {
    return true;
  }

  public function rules(): array
  {
    return [
      'cancion' => 'sometimes|string|max:255',
      'letra' => 'sometimes|string',
      'youtube' => 'nullable|string',
      'spotify' => 'nullable|string',
      'interprete_id' => 'sometimes|exists:interpretes,id',
      'publicar' => 'nullable|date',
      'user_id' => 'sometimes|exists:users,id',
      'estado' => 'nullable|string',
    ];
  }
}
