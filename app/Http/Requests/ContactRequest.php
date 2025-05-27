<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ContactRequest extends FormRequest
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
      'address' =>  'required|string|min:3|max:255',
      'phone' =>  'required|string|min:5|max:200',
      'email' =>  'required|email',
      'facebook' =>  'nullable|string|url|max:255',
      'instagram' =>  'nullable|url|string|max:255',
      'youtube' =>  'nullable|string|url|max:255',
      'whatsapp' =>  'nullable|string|max:200',
      'map' =>  'nullable|string|max:255',
    ];
  }

  public function messages()
  {
    return [
      'address.required' => 'El domicilio es requerido',
      'address.min' => 'El domicilio debe tener al menos 3 caracteres',
      'address.max' => 'El domicilio debe tener máximo 255 caracteres',
      'phone.required' => 'El teléfono es requerido',
      'phone.min' => 'El teléfono debe tener al menos 5 caracteres',
      'phone.max' => 'El teléfono debe tener máximo 200 caracteres',
      'email.required' => 'El correo electrónico es requerido',
      'email.email' => 'El correo electrónico no es válido',
      'facebook.max' => 'El enlace de Facebook debe tener máximo 255 caracteres',
      'facebook.url' => 'El enlace de Facebook no es válido',
      'instagram.max' => 'El enlace de Instagram debe tener máximo 255 caracteres',
      'instagram.url' => 'El enlace de Instagram no es válido',
      'youtube.max' => 'El enlace de Youtube debe tener máximo 255 caracteres',
      'youtube.url' => 'El enlace de Youtube no es válido',
      'whatsapp.max' => 'Máximo permitido 200 caracteres',
      'map.max' => 'El enlace de Google Maps debe tener máximo 255 caracteres',
    ];
  }
}
