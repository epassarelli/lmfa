<?php

namespace App\Traits;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

/**
 * Contenido con interprete_id (discos, canciones) limitado a lo que un usuario carga o a los
 * intérpretes que administra por membresía.
 */
trait ScopesToManagedInterprete
{
    public function scopeOwnedOrOfManagedInterprete(Builder $query, User $user): Builder
    {
        return $query->where(function (Builder $inner) use ($user) {
            $inner->where($this->qualifyColumn('user_id'), $user->getKey())
                ->orWhereIn($this->qualifyColumn('interprete_id'), $user->managedInterpreteIdsSubquery());
        });
    }
}
