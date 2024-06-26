<?php

namespace App\Policies;

use App\Models\Mito;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class MitoPolicy
{
  use HandlesAuthorization;

  public function viewAny(User $user)
  {
    return true;
  }

  public function view(User $user, Mito $mito)
  {
    return $user->hasRole('administrador') || $mito->user_id == $user->id;
  }

  public function create(User $user)
  {
    return $user->hasAnyRole(['administrador', 'prensa', 'colaborador']);
  }

  public function update(User $user, Mito $mito)
  {
    return $user->hasRole('administrador') || $mito->user_id == $user->id;
  }

  public function delete(User $user, Mito $mito)
  {
    return $user->hasRole('administrador');
  }
}
