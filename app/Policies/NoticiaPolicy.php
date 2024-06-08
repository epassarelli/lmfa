<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Noticia;
use Illuminate\Auth\Access\HandlesAuthorization;

class NoticiaPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        return $user->can('read noticia');
    }

    public function view(User $user, Noticia $noticia)
    {
        return $user->can('read noticia');
    }

    public function create(User $user)
    {
        return $user->can('create noticia');
    }

    public function update(User $user, Noticia $noticia)
    {
        return $user->can('update noticia');
    }

    public function delete(User $user, Noticia $noticia)
    {
        return $user->can('delete noticia');
    }
}
