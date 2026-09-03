<?php

namespace App\Policies;

use App\Models\RadioSignal;
use App\Models\User;

class RadioSignalPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, RadioSignal $signal): bool
    {
        return $user->hasRole('administrador') || $signal->created_by === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['administrador', 'prensa', 'colaborador']);
    }

    public function update(User $user, RadioSignal $signal): bool
    {
        return $user->hasRole('administrador')
            || ($signal->created_by === $user->id && $signal->editorial_status === 'draft');
    }

    public function publish(User $user, RadioSignal $signal): bool
    {
        return $user->hasRole('administrador');
    }

    public function delete(User $user, RadioSignal $signal): bool
    {
        return $user->hasRole('administrador');
    }
}
