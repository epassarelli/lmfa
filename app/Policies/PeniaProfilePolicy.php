<?php

namespace App\Policies;

use App\Models\PeniaProfile;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PeniaProfilePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, PeniaProfile $profile): bool
    {
        return $user->hasRole('administrador') || $profile->created_by === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['administrador', 'prensa', 'colaborador']);
    }

    public function update(User $user, PeniaProfile $profile): bool
    {
        return $user->hasRole('administrador') || $profile->created_by === $user->id;
    }

    public function delete(User $user, PeniaProfile $profile): bool
    {
        return $user->hasRole('administrador');
    }
}
