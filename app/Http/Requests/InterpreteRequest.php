<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class InterpreteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules()
    {
        $rules = [
            'interprete' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'biografia' => 'required|string',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg|max:512',
            'correo' => 'nullable|email|max:255',
            'telefono' => 'nullable|string|max:255',
            'facebook' => 'nullable|url',
            'instagram' => 'nullable|url',
            'twitter' => 'nullable|url',
            'web' => 'nullable|url',
            'estado' => 'nullable|boolean',
        ];

        if ($this->isMethod('post')) {
            $rules['foto'] = 'required|image|mimes:jpeg,png,jpg|max:512';
        }

        return $rules;
    }
}
