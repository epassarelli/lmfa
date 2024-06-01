<?php

namespace App\Actions\Jetstream;

<<<<<<< HEAD
=======
<<<<<<< HEAD
=======
>>>>>>> f6a1528eb1fc36098adb0d1c90a2861b3666f2d7
use App\Models\Team;
use App\Models\User;
use Closure;
use Illuminate\Database\Query\Builder;
<<<<<<< HEAD
=======
>>>>>>> dev
>>>>>>> f6a1528eb1fc36098adb0d1c90a2861b3666f2d7
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Jetstream\Contracts\InvitesTeamMembers;
use Laravel\Jetstream\Events\InvitingTeamMember;
use Laravel\Jetstream\Jetstream;
use Laravel\Jetstream\Mail\TeamInvitation;
use Laravel\Jetstream\Rules\Role;

class InviteTeamMember implements InvitesTeamMembers
{
    /**
     * Invite a new team member to the given team.
<<<<<<< HEAD
     */
    public function invite(User $user, Team $team, string $email, string $role = null): void
=======
<<<<<<< HEAD
     *
     * @param  mixed  $user
     * @param  mixed  $team
     * @param  string  $email
     * @param  string|null  $role
     * @return void
     */
    public function invite($user, $team, string $email, string $role = null)
=======
     */
    public function invite(User $user, Team $team, string $email, string $role = null): void
>>>>>>> dev
>>>>>>> f6a1528eb1fc36098adb0d1c90a2861b3666f2d7
    {
        Gate::forUser($user)->authorize('addTeamMember', $team);

        $this->validate($team, $email, $role);

        InvitingTeamMember::dispatch($team, $email, $role);

        $invitation = $team->teamInvitations()->create([
            'email' => $email,
            'role' => $role,
        ]);

        Mail::to($email)->send(new TeamInvitation($invitation));
    }

    /**
     * Validate the invite member operation.
<<<<<<< HEAD
     */
    protected function validate(Team $team, string $email, ?string $role): void
=======
<<<<<<< HEAD
     *
     * @param  mixed  $team
     * @param  string  $email
     * @param  string|null  $role
     * @return void
     */
    protected function validate($team, string $email, ?string $role)
=======
     */
    protected function validate(Team $team, string $email, ?string $role): void
>>>>>>> dev
>>>>>>> f6a1528eb1fc36098adb0d1c90a2861b3666f2d7
    {
        Validator::make([
            'email' => $email,
            'role' => $role,
        ], $this->rules($team), [
            'email.unique' => __('This user has already been invited to the team.'),
        ])->after(
            $this->ensureUserIsNotAlreadyOnTeam($team, $email)
        )->validateWithBag('addTeamMember');
    }

    /**
     * Get the validation rules for inviting a team member.
     *
<<<<<<< HEAD
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
=======
<<<<<<< HEAD
     * @param  mixed  $team
     * @return array
>>>>>>> f6a1528eb1fc36098adb0d1c90a2861b3666f2d7
     */
    protected function rules(Team $team): array
    {
        return array_filter([
<<<<<<< HEAD
=======
            'email' => ['required', 'email', Rule::unique('team_invitations')->where(function ($query) use ($team) {
                $query->where('team_id', $team->id);
            })],
=======
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    protected function rules(Team $team): array
    {
        return array_filter([
>>>>>>> f6a1528eb1fc36098adb0d1c90a2861b3666f2d7
            'email' => [
                'required', 'email',
                Rule::unique('team_invitations')->where(function (Builder $query) use ($team) {
                    $query->where('team_id', $team->id);
                }),
            ],
<<<<<<< HEAD
=======
>>>>>>> dev
>>>>>>> f6a1528eb1fc36098adb0d1c90a2861b3666f2d7
            'role' => Jetstream::hasRoles()
                            ? ['required', 'string', new Role]
                            : null,
        ]);
    }

    /**
     * Ensure that the user is not already on the team.
<<<<<<< HEAD
     */
    protected function ensureUserIsNotAlreadyOnTeam(Team $team, string $email): Closure
=======
<<<<<<< HEAD
     *
     * @param  mixed  $team
     * @param  string  $email
     * @return \Closure
     */
    protected function ensureUserIsNotAlreadyOnTeam($team, string $email)
=======
     */
    protected function ensureUserIsNotAlreadyOnTeam(Team $team, string $email): Closure
>>>>>>> dev
>>>>>>> f6a1528eb1fc36098adb0d1c90a2861b3666f2d7
    {
        return function ($validator) use ($team, $email) {
            $validator->errors()->addIf(
                $team->hasUserWithEmail($email),
                'email',
                __('This user already belongs to the team.')
            );
        };
    }
}
