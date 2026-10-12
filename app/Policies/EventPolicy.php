<?php

namespace App\Policies;

use App\Models\Event;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class EventPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user)
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Event $event)
    {
        return $user->hasRole('administrador') || $event->created_by == $user->id || $user->managesAllInterpretes($event->linkedInterpreteIds());
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user)
    {
        return $user->hasAnyRole(['administrador', 'colaborador']) || $user->managedInterpreteIds() !== [];
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Event $event)
    {
        return $user->hasRole('administrador') || $event->created_by == $user->id || $user->managesAllInterpretes($event->linkedInterpreteIds());
    }

    /**
     * Jornadas (EventProgram): administrador, creador, o quien puede editar todos los eventos del programa.
     */
    public function updateProgram(User $user, \App\Models\EventProgram $program): bool
    {
        if ($user->hasRole('administrador') || (int) $program->created_by === (int) $user->id) {
            return true;
        }

        $events = $program->events()->get();

        return $events->isNotEmpty() && $events->every(fn (Event $event) => $this->update($user, $event));
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Event $event)
    {
        return $user->hasRole('administrador');
    }
}
