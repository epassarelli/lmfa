<?php

namespace App\Policies;

use App\Models\RadioProgram;
use App\Models\User;

class RadioProgramPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, RadioProgram $program): bool
    {
        return $user->hasRole('administrador') || $program->created_by === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['administrador', 'prensa', 'colaborador']);
    }

    public function update(User $user, RadioProgram $program): bool
    {
        return $user->hasRole('administrador')
            || ($program->created_by === $user->id && $program->editorial_status === 'draft');
    }

    public function publish(User $user, RadioProgram $program): bool
    {
        return $user->hasRole('administrador');
    }

    public function delete(User $user, RadioProgram $program): bool
    {
        return $user->hasRole('administrador');
    }
}
