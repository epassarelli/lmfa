<?php

namespace App\Policies;

use App\Models\Cancion;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CancionPolicy
{
  use HandlesAuthorization;

  public function viewAny(User $user)
  {
    return true;
  }

  public function view(User $user, Cancion $cancion)
  {
    // return $user->hasRole('administrador') || $cancion->user_id == $user->id;
    return $user->can('read cancion');
  }

  public function create(User $user)
  {
    // return $user->hasAnyRole(['administrador', 'prensa', 'colaborador']);
    return $user->can('create cancion');
  }

  public function update(User $user, Cancion $cancion)
  {
    // return $user->hasRole('administrador') || $cancion->user_id == $user->id;
    return $user->can('update cancion');
  }

  public function delete(User $user, Cancion $cancion)
  {
    // return $user->hasRole('administrador');
    return $user->can('delete cancion');
  }
}
