<?php

namespace App\Policies;

use App\Models\Team;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class TeamPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
<<<<<<< HEAD
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function viewAny(User $user)
=======
     */
    public function viewAny(User $user): bool
>>>>>>> dev
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
<<<<<<< HEAD
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Team  $team
     * @return mixed
     */
    public function view(User $user, Team $team)
=======
     */
    public function view(User $user, Team $team): bool
>>>>>>> dev
    {
        return $user->belongsToTeam($team);
    }

    /**
     * Determine whether the user can create models.
<<<<<<< HEAD
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function create(User $user)
=======
     */
    public function create(User $user): bool
>>>>>>> dev
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
<<<<<<< HEAD
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Team  $team
     * @return mixed
     */
    public function update(User $user, Team $team)
=======
     */
    public function update(User $user, Team $team): bool
>>>>>>> dev
    {
        return $user->ownsTeam($team);
    }

    /**
     * Determine whether the user can add team members.
<<<<<<< HEAD
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Team  $team
     * @return mixed
     */
    public function addTeamMember(User $user, Team $team)
=======
     */
    public function addTeamMember(User $user, Team $team): bool
>>>>>>> dev
    {
        return $user->ownsTeam($team);
    }

    /**
     * Determine whether the user can update team member permissions.
<<<<<<< HEAD
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Team  $team
     * @return mixed
     */
    public function updateTeamMember(User $user, Team $team)
=======
     */
    public function updateTeamMember(User $user, Team $team): bool
>>>>>>> dev
    {
        return $user->ownsTeam($team);
    }

    /**
     * Determine whether the user can remove team members.
<<<<<<< HEAD
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Team  $team
     * @return mixed
     */
    public function removeTeamMember(User $user, Team $team)
=======
     */
    public function removeTeamMember(User $user, Team $team): bool
>>>>>>> dev
    {
        return $user->ownsTeam($team);
    }

    /**
     * Determine whether the user can delete the model.
<<<<<<< HEAD
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Team  $team
     * @return mixed
     */
    public function delete(User $user, Team $team)
=======
     */
    public function delete(User $user, Team $team): bool
>>>>>>> dev
    {
        return $user->ownsTeam($team);
    }
}
