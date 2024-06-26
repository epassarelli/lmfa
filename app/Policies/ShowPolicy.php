<?php

namespace App\Policies;

use App\Models\Show;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ShowPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        return true;
    }

    public function view(User $user, Show $show)
    {
        return $user->hasRole('administrador') || $show->user_id == $user->id;
    }

    public function create(User $user)
    {
        return $user->hasAnyRole(['administrador', 'prensa', 'colaborador']);
    }

    public function update(User $user, Show $show)
    {
        return $user->hasRole('administrador') || $show->user_id == $user->id;
    }

    public function delete(User $user, Show $show)
    {
        return $user->hasRole('administrador');
    }
}
