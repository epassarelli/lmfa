<?php

namespace App\Actions\Jetstream;

<<<<<<< HEAD
use App\Models\Team;
use App\Models\User;
=======
<<<<<<< HEAD
=======
use App\Models\Team;
use App\Models\User;
>>>>>>> dev
>>>>>>> f6a1528eb1fc36098adb0d1c90a2861b3666f2d7
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Laravel\Jetstream\Contracts\RemovesTeamMembers;
use Laravel\Jetstream\Events\TeamMemberRemoved;

class RemoveTeamMember implements RemovesTeamMembers
{
    /**
     * Remove the team member from the given team.
<<<<<<< HEAD
     */
    public function remove(User $user, Team $team, User $teamMember): void
=======
<<<<<<< HEAD
     *
     * @param  mixed  $user
     * @param  mixed  $team
     * @param  mixed  $teamMember
     * @return void
     */
    public function remove($user, $team, $teamMember)
=======
     */
    public function remove(User $user, Team $team, User $teamMember): void
>>>>>>> dev
>>>>>>> f6a1528eb1fc36098adb0d1c90a2861b3666f2d7
    {
        $this->authorize($user, $team, $teamMember);

        $this->ensureUserDoesNotOwnTeam($teamMember, $team);

        $team->removeUser($teamMember);

        TeamMemberRemoved::dispatch($team, $teamMember);
    }

    /**
     * Authorize that the user can remove the team member.
<<<<<<< HEAD
     */
    protected function authorize(User $user, Team $team, User $teamMember): void
=======
<<<<<<< HEAD
     *
     * @param  mixed  $user
     * @param  mixed  $team
     * @param  mixed  $teamMember
     * @return void
     */
    protected function authorize($user, $team, $teamMember)
=======
     */
    protected function authorize(User $user, Team $team, User $teamMember): void
>>>>>>> dev
>>>>>>> f6a1528eb1fc36098adb0d1c90a2861b3666f2d7
    {
        if (! Gate::forUser($user)->check('removeTeamMember', $team) &&
            $user->id !== $teamMember->id) {
            throw new AuthorizationException;
        }
    }

    /**
     * Ensure that the currently authenticated user does not own the team.
<<<<<<< HEAD
     */
    protected function ensureUserDoesNotOwnTeam(User $teamMember, Team $team): void
=======
<<<<<<< HEAD
     *
     * @param  mixed  $teamMember
     * @param  mixed  $team
     * @return void
     */
    protected function ensureUserDoesNotOwnTeam($teamMember, $team)
=======
     */
    protected function ensureUserDoesNotOwnTeam(User $teamMember, Team $team): void
>>>>>>> dev
>>>>>>> f6a1528eb1fc36098adb0d1c90a2861b3666f2d7
    {
        if ($teamMember->id === $team->owner->id) {
            throw ValidationException::withMessages([
                'team' => [__('You may not leave a team that you created.')],
            ])->errorBag('removeTeamMember');
        }
    }
}
