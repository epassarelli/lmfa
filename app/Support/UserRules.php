<?php

namespace App\Support;

use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

/**
 * Reglas de validación de los datos de un usuario, compartidas entre el CRUD de Usuarios del
 * backoffice (administrador) y "Mis datos" (cada persona sobre sí misma).
 */
class UserRules
{
    public const PASSWORD_MIN = 8;

    public static function name(): array
    {
        return ['required', 'string', 'max:255'];
    }

    public static function phone(): array
    {
        return ['nullable', 'string', 'max:50'];
    }

    public static function email(?int $ignoreId = null): array
    {
        return ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($ignoreId)];
    }

    public static function newPassword(bool $required = true): array
    {
        return [$required ? 'required' : 'nullable', 'confirmed', Password::min(self::PASSWORD_MIN)];
    }
}
