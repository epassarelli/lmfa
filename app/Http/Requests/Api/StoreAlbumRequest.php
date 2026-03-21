<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class StoreAlbumRequest extends FormRequest
{
  public function authorize(): bool
  {
    return true;
  }

  public function rules(): array
  {
    return [
      'album' => 'required|string|max:255',
      'anio' => 'required|digits:4',
      'spotify' => 'nullable|string',
      'interprete_id' => 'required|exists:interpretes,id',
      'foto' => 'nullable|string',
      'user_id' => 'required|exists:users,id',
      'estado' => 'nullable|string',
    ];
  }
}
