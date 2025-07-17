<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class NoticiaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $noticia = $this->route('noticia'); // null en store, objeto en update

        return [
            'titulo' => 'required|string|max:255',
            'slug' => [
                'required',
                'string',
                Rule::unique('noticias', 'slug')->ignore($noticia?->id),
            ],
            'noticia' => 'required|string',
            'foto' => $noticia ? 'nullable|image' : 'required|image',
            'interprete_principal_id' => 'nullable|exists:interpretes,id',
            'interprete_secundarios' => 'nullable|array',
            'interprete_secundarios.*' => 'exists:interpretes,id',
            'categoria_id' => 'required|exists:categorias,id',
            'publicar' => 'nullable|date',
        ];
    }



    public function messages(): array
    {
        return [
            'titulo.required' => 'El título es obligatorio.',
            'slug.required' => 'El slug es obligatorio.',
            'slug.unique' => 'El slug ya está en uso.',
            'noticia.required' => 'El contenido de la noticia es obligatorio.',
            'foto.required' => 'La imagen destacada es obligatoria.',
            'foto.image' => 'El archivo debe ser una imagen válida.',
            'categoria_id.required' => 'Debe seleccionar una categoría.',
            'categoria_id.exists' => 'La categoría seleccionada no existe.',

            'interprete_principal_id.exists' => 'El intérprete principal seleccionado no es válido.',
            'interprete_id.array' => 'El campo de intérpretes secundarios debe ser un array.',
            'interprete_id.*.exists' => 'Uno de los intérpretes secundarios no existe en la base de datos.',
        ];
    }
}
