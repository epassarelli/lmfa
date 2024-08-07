<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AlbumRequest extends FormRequest
{
  public function authorize()
  {
    return true;
  }

  public function rules()
  {
    return [
      'album' => 'required|string|max:255',
      'anio' => 'required|digits:4',
      'spotify' => 'nullable|string',
      'interprete_id' => 'required|exists:interpretes,id',
      'foto' => 'image|mimes:jpeg,jpg|max:200',
    ];
  }
}
