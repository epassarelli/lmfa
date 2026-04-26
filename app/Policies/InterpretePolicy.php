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
        return $user->hasRole('administrador') || $user->can('create interprete');
    }

    public function update(User $user, Interprete $interprete)
    {
        return $user->hasRole('administrador') || $interprete->user_id == $user->id;
    }

    public function delete(User $user, Interprete $interprete)
    {
        return $user->hasRole('administrador');
    }
}
