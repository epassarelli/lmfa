<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAlbumRequest extends FormRequest
{
  public function authorize(): bool
  {
    return true;
  }

  public function rules(): array
  {
    return [
      'album' => 'sometimes|string|max:255',
      'anio' => 'sometimes|digits:4',
      'spotify' => 'nullable|string',
      'interprete_id' => 'sometimes|exists:interpretes,id',
      'foto' => 'nullable|string',
      'user_id' => 'sometimes|exists:users,id',
      'estado' => 'nullable|string',
    ];
  }
}
