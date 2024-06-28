<?php

namespace App\Policies;

use App\Models\Festival;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class FestivalPolicy
{
  use HandlesAuthorization;

  public function viewAny(User $user)
  {
    return true;
  }

  public function view(User $user, Festival $festival)
  {
    return $user->hasRole('administrador') || $festival->user_id == $user->id;
  }

  public function create(User $user)
  {
    return $user->hasAnyRole(['administrador', 'prensa', 'colaborador']);
  }

  public function update(User $user, Festival $festival)
  {
    return $user->hasRole('administrador') || $festival->user_id == $user->id;
  }

  public function delete(User $user, Festival $festival)
  {
    return $user->hasRole('administrador');
  }
}
