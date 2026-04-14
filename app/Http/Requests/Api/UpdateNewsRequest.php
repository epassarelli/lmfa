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
      // Nombres canónicos (recomendados para integraciones nuevas)
      'title'                => 'sometimes|string|max:255',
      'body'                 => 'sometimes|string',
      'featured_image_path'  => 'nullable|string',
      'created_by'           => 'sometimes|exists:users,id',
      'published_at'         => 'nullable|date',
      'editorial_status'     => 'nullable|string',
      // Nombres legacy (compatibilidad con integraciones existentes)
      'titulo'       => 'sometimes|string|max:255',
      'noticia'      => 'sometimes|string',
      'foto'         => 'nullable|string',
      'user_id'      => 'sometimes|exists:users,id',
      'publicar'     => 'nullable',
      // Campos comunes (ambos formatos)
      'categoria_id'  => 'sometimes|exists:categorias,id',
      'interprete_id' => 'nullable|exists:interpretes,id',
      'visitas'       => 'nullable|integer',
      'estado'        => 'nullable|string',
    ];
  }
}
