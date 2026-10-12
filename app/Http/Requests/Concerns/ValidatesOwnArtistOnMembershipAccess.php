<?php

namespace App\Http\Requests\Concerns;

use Illuminate\Contracts\Validation\Validator;

/**
 * Si el acceso a un evento/noticia viene solo de la membresía de una ficha de artista, el registro
 * debe incluir al menos un artista que el usuario administra (puede sumar otros como invitados).
 */
trait ValidatesOwnArtistOnMembershipAccess
{
    /**
     * @param  array<int, int|string|null>  $artistIds
     */
    protected function requireOwnArtist(Validator $validator, bool $membershipOnly, array $artistIds, string $field): void
    {
        if (! $membershipOnly) {
            return;
        }

        $validator->after(function (Validator $validator) use ($artistIds, $field) {
            if (! $this->user()?->managesAnyInterprete($artistIds)) {
                $validator->errors()->add($field, 'Tenés que incluir al menos un artista que administrás.');
            }
        });
    }
}
