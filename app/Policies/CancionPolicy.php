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
    return $user->can('read cancion') || $user->canManageInterpreteId($cancion->interprete_id);
  }

  public function create(User $user)
  {
    // return $user->hasAnyRole(['administrador', 'colaborador']);
    return $user->can('create cancion') || $user->managedInterpreteIds() !== [];
  }

  public function update(User $user, Cancion $cancion)
  {
    // return $user->hasRole('administrador') || $cancion->user_id == $user->id;
    return (int) $cancion->user_id === (int) $user->id || $user->canManageInterpreteId($cancion->interprete_id);
  }

  public function delete(User $user, Cancion $cancion)
  {
    // return $user->hasRole('administrador');
    return $user->can('delete cancion');
  }
}
