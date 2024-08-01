<?php

namespace App\Policies;

use App\Models\Album;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class AlbumPolicy
{
  use HandlesAuthorization;

  public function viewAny(User $user)
  {
    return true;
  }

  public function view(User $user, Album $album)
  {
    // return $user->hasRole('administrador') || $album->user_id == $user->id;
    return $user->can('read album');
  }

  public function create(User $user)
  {
    // return $user->hasAnyRole(['administrador', 'prensa', 'colaborador']);
    return $user->can('create album');
  }

  public function update(User $user, Album $album)
  {
    // return $user->hasRole('administrador') || $album->user_id == $user->id;
    return $user->can('update album');
  }

  public function delete(User $user, Album $album)
  {
    // return $user->hasRole('administrador');
    return $user->can('delete album');
  }
}
