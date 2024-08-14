<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreClassifiedRequest extends FormRequest
{
    public function authorize()
    {
        return true; // Cambiar según la lógica de autorización
    }

    public function rules()
    {
        return [
            'category_id'    => 'required|exists:categories,id',
            'title'          => 'required|string|max:255',
            'description'    => 'required|string',
            'price'          => 'nullable|numeric',
            'location'       => 'required|string|max:255',
            'contact_info'   => 'required|string|max:255',
            'expiration_date' => 'nullable|date|after:today',
            'images.*'       => 'nullable|image|max:2048',
            'tags'           => 'nullable|array',
            'tags.*'         => 'exists:tags,id',
        ];
    }
}
