<?php

namespace App\Policies;

use App\Models\Comida;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ComidaPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        return true;
    }

    public function view(User $user, Comida $comida)
    {
        return $user->hasRole('administrador') || $comida->user_id == $user->id;
    }

    public function create(User $user)
    {
        return $user->hasAnyRole(['administrador', 'prensa', 'colaborador']);
    }

    public function update(User $user, Comida $comida)
    {
        return $user->hasRole('administrador') || $comida->user_id == $user->id;
    }

    public function delete(User $user, Comida $comida)
    {
        return $user->hasRole('administrador');
    }
}
