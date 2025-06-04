<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ShowRequest extends FormRequest
{
  public function authorize()
  {
    return true;
  }

  public function rules(): array
  {
    return [
      'show' => 'required|string|max:255',
      'detalles' => 'nullable|string',
      'fecha' => 'required|date',
      'hora' => 'nullable|string|max:10',
      'lugar' => 'nullable|string|max:255',
      'direccion' => 'nullable|string|max:255',
      'interprete_id' => 'nullable|exists:interpretes,id',

      // Nuevos campos
      'precio_entrada' => 'nullable|string|max:100',
      'link_entradas' => 'nullable|url|max:255',
      'destacado' => 'nullable|boolean',
      'imagen_destacada' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
      'slug' => 'nullable|string|max:255|unique:shows,slug,' . $this->route('show'),
      'lat' => 'nullable|numeric|between:-90,90',
      'lng' => 'nullable|numeric|between:-180,180',
      'provincia_id' => 'nullable|exists:provincias,id',
    ];
  }
}
