<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class UpdateNewsRequest extends FormRequest
{
  /**
   * Determine if the user is authorized to make this request.
   */
  public function authorize(): bool
  {
    return true;
  }

  /**
   * Get the validation rules that apply to the request.
   *
   * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
   */
  public function rules(): array
  {
    return [
      'titulo' => 'sometimes|string|max:255',
      'categoria_id' => 'sometimes|exists:categorias,id',
      'noticia' => 'sometimes|string',
      'interprete_id' => 'nullable|exists:interpretes,id',
      'foto' => 'nullable|string',
      'visitas' => 'nullable|integer',
      'publicar' => 'boolean',
      'user_id' => 'sometimes|exists:users,id',
      'estado' => 'nullable|string',
    ];
  }
}
