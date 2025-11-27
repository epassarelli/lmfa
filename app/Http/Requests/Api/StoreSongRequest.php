<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class StoreSongRequest extends FormRequest
{
  public function authorize(): bool
  {
    return true;
  }

  public function rules(): array
  {
    return [
      'cancion' => 'required|string|max:255',
      'letra' => 'required|string',
      'youtube' => 'nullable|string',
      'spotify' => 'nullable|string',
      'interprete_id' => 'required|exists:interpretes,id',
      'publicar' => 'nullable|date',
      'user_id' => 'required|exists:users,id',
      'estado' => 'nullable|string',
    ];
  }
}
