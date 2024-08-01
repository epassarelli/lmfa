<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CancionRequest extends FormRequest
{
  public function authorize()
  {
    return true;
  }

  public function rules()
  {
    return [
      'cancion' => 'required|string|max:255',
      'letra' => 'required|string',
      'youtube' => 'nullable|url',
      'spotify' => 'nullable|url',
      'interprete_id' => 'required|exists:interpretes,id',
      // 'album_id' => 'required|exists:albunes,id',
      'publicar' => 'nullable|date',
    ];
  }
}
