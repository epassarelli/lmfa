<?php

namespace App\Http\Requests\Concerns;

/**
 * Regla de interprete_id para contenido (discos, canciones): quien no tiene permiso global
 * de escritura solo puede elegir intérpretes que administra por membresía.
 */
trait ValidatesManagedInterprete
{
    /**
     * @param  string  $content  nombre del recurso y del parámetro de ruta ('album', 'cancion')
     */
    protected function interpreteRules(string $content): array
    {
        return [
            'required',
            'exists:interpretes,id',
            function (string $attribute, mixed $value, \Closure $fail) use ($content) {
                $user = $this->user();
                $record = $this->route($content);

                // Quien edita un registro propio (lo creo el) no queda limitado a los artistas que administra.
                if ($user && $record && (int) $record->user_id === (int) $user->id) {
                    return;
                }

                $permission = ($record ? 'update ' : 'create ').$content;

                if ($user && $user->isScopedToManagedInterpretes($permission) && ! $user->canManageInterpreteId((int) $value)) {
                    $fail('No administrás este artista.');
                }
            },
        ];
    }
}
