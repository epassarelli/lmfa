<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class OrganizationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('administrador') ?? false;
    }

    public function rules(): array
    {
        $organization = $this->route('organization');

        return [
            'type' => ['required', Rule::in(array_keys(config('entity_management.organization_types')))],
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', Rule::unique('organizations', 'slug')->ignore($organization?->id)],
            'legal_name' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'website' => ['nullable', 'url', 'max:255'],
            'province_id' => ['nullable', 'integer', 'exists:provincias,id'],
            'city' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
            'source' => ['nullable', Rule::in(config('entity_management.sources'))],
            'contact_consent' => ['nullable', 'boolean'],
            'contact_consent_purpose' => ['nullable', 'string', 'max:100'],
            'unsubscribed' => ['nullable', 'boolean'],
            'is_verified' => ['nullable', 'boolean'],
            'verification_method' => ['nullable', Rule::in(config('entity_management.verification_methods'))],
        ];
    }

    public function attributes(): array
    {
        return [
            'type' => 'tipo',
            'name' => 'nombre',
            'slug' => 'slug',
            'legal_name' => 'razón social',
            'phone' => 'teléfono',
            'website' => 'sitio web',
            'province_id' => 'provincia',
            'city' => 'ciudad',
            'address' => 'dirección',
            'status' => 'estado',
        ];
    }
}
