<?php

namespace App\Http\Requests\Frontend;

use Illuminate\Foundation\Http\FormRequest;

class ContactMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'lastName' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'issue' => 'required|string|max:255',
            'message' => 'required|string',
            'captcha' => 'required|integer|in:7', // Simple static captcha: 3 + 4 = 7
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'El nombre es obligatorio.',
            'lastName.required' => 'El apellido es obligatorio.',
            'email.required' => 'El email es obligatorio.',
            'email.email' => 'El email no es válido.',
            'issue.required' => 'El asunto es obligatorio.',
            'message.required' => 'El mensaje es obligatorio.',
            'captcha.required' => 'El captcha es obligatorio.',
            'captcha.in' => 'El resultado del captcha es incorrecto.',
        ];
    }
}
