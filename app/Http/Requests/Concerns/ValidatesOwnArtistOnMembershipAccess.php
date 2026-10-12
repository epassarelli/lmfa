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
     * Eventos: alcanza con incluir un artista o un festival que el usuario administra.
     *
     * @param  array<int, int|string|null>  $artistIds
     * @param  array<int, int|string|null>  $festivalIds
     */
    protected function requireOwnArtistOrFestival(Validator $validator, bool $membershipOnly, array $artistIds, array $festivalIds, string $field): void
    {
        if (! $membershipOnly) {
            return;
        }

        $validator->after(function (Validator $validator) use ($artistIds, $festivalIds, $field) {
            $user = $this->user();
            $festivalIds = array_map('intval', array_filter($festivalIds));
            $ownsFestival = array_intersect($festivalIds, $user?->managedFestivalIds() ?? []) !== [];

            if (! $ownsFestival && ! $user?->managesAnyInterprete($artistIds)) {
                $validator->errors()->add($field, 'Tenés que incluir al menos un artista o festival que administrás.');
            }
        });
    }

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
