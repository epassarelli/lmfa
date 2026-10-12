<?php

namespace App\Policies;

use App\Models\Interprete;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class InterpretePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        return true; // Todos ven el listado
    }

    public function view(User $user, Interprete $interprete)
    {
        return true;
    }

    public function create(User $user)
    {
        return $user->hasRole('administrador') || ($user->can('create interprete') && $user->canCreateEntityDirectly(['colaborador']));
    }

    public function update(User $user, Interprete $interprete)
    {
        return $user->canManage($interprete);
    }

    public function delete(User $user, Interprete $interprete)
    {
        return $user->hasRole('administrador');
    }
}
