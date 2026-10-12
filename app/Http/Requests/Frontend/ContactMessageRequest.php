<?php

namespace App\Http\Requests\Frontend;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ContactMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => is_string($this->input('name')) ? trim(preg_replace('/\s+/u', ' ', $this->input('name'))) : $this->input('name'),
            'email' => is_string($this->input('email')) ? Str::lower(trim($this->input('email'))) : $this->input('email'),
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150', 'not_regex:/[\r\n]/'],
            'email' => ['required', 'email:rfc', 'max:255'],
            'issue' => ['required', 'string', Rule::in(config('contact.subjects'))],
            'message' => ['required', 'string', 'min:'.config('contact.message_min'), 'max:'.config('contact.message_max')],
            'consent' => ['accepted'],
            'form_token' => ['required', 'string'],
            'website' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Contanos tu nombre y apellido.',
            'name.max' => 'El nombre es demasiado largo.',
            'name.not_regex' => 'El nombre no puede tener saltos de línea.',
            'email.required' => 'Necesitamos tu email para poder responderte.',
            'email.email' => 'El email no es válido.',
            'issue.required' => 'Elegí un asunto.',
            'issue.in' => 'Elegí un asunto de la lista.',
            'message.required' => 'Escribí tu mensaje.',
            'message.min' => 'El mensaje es muy corto: contanos un poco más (mínimo :min caracteres).',
            'message.max' => 'El mensaje es demasiado largo (máximo :max caracteres).',
            'consent.accepted' => 'Tenés que aceptar la política de privacidad para enviar el mensaje.',
            'form_token.required' => 'No pudimos validar el formulario. Recargá la página y volvé a intentar.',
        ];
    }
}
