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
     */
    public function viewAny(User $user): bool
=======
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
>>>>>>> f6a1528eb1fc36098adb0d1c90a2861b3666f2d7
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
<<<<<<< HEAD
     */
    public function view(User $user, Team $team): bool
=======
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
>>>>>>> f6a1528eb1fc36098adb0d1c90a2861b3666f2d7
    {
        return $user->belongsToTeam($team);
    }

    /**
     * Determine whether the user can create models.
<<<<<<< HEAD
     */
    public function create(User $user): bool
=======
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
>>>>>>> f6a1528eb1fc36098adb0d1c90a2861b3666f2d7
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
<<<<<<< HEAD
     */
    public function update(User $user, Team $team): bool
=======
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
>>>>>>> f6a1528eb1fc36098adb0d1c90a2861b3666f2d7
    {
        return $user->ownsTeam($team);
    }

    /**
     * Determine whether the user can add team members.
<<<<<<< HEAD
     */
    public function addTeamMember(User $user, Team $team): bool
=======
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
>>>>>>> f6a1528eb1fc36098adb0d1c90a2861b3666f2d7
    {
        return $user->ownsTeam($team);
    }

    /**
     * Determine whether the user can update team member permissions.
<<<<<<< HEAD
     */
    public function updateTeamMember(User $user, Team $team): bool
=======
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
>>>>>>> f6a1528eb1fc36098adb0d1c90a2861b3666f2d7
    {
        return $user->ownsTeam($team);
    }

    /**
     * Determine whether the user can remove team members.
<<<<<<< HEAD
     */
    public function removeTeamMember(User $user, Team $team): bool
=======
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
>>>>>>> f6a1528eb1fc36098adb0d1c90a2861b3666f2d7
    {
        return $user->ownsTeam($team);
    }

    /**
     * Determine whether the user can delete the model.
<<<<<<< HEAD
     */
    public function delete(User $user, Team $team): bool
=======
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
>>>>>>> f6a1528eb1fc36098adb0d1c90a2861b3666f2d7
    {
        return $user->ownsTeam($team);
    }
}
