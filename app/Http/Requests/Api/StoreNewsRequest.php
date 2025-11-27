<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class StoreNewsRequest extends FormRequest
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
      'titulo' => 'required|string|max:255',
      'categoria_id' => 'required|exists:categorias,id',
      'noticia' => 'required|string',
      'interprete_id' => 'nullable|exists:interpretes,id',
      'foto' => 'nullable|string', // Assuming URL or path string for now
      'visitas' => 'nullable|integer',
      'publicar' => 'boolean',
      'user_id' => 'required|exists:users,id',
      'estado' => 'nullable|string',
    ];
  }
}
