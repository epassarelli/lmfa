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
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Laravel\Jetstream\Contracts\UpdatesTeamNames;

class UpdateTeamName implements UpdatesTeamNames
{
    /**
     * Validate and update the given team's name.
     *
<<<<<<< HEAD
     * @param  array<string, string>  $input
     */
    public function update(User $user, Team $team, array $input): void
=======
<<<<<<< HEAD
     * @param  mixed  $user
     * @param  mixed  $team
     * @param  array  $input
     * @return void
     */
    public function update($user, $team, array $input)
=======
     * @param  array<string, string>  $input
     */
    public function update(User $user, Team $team, array $input): void
>>>>>>> dev
>>>>>>> f6a1528eb1fc36098adb0d1c90a2861b3666f2d7
    {
        Gate::forUser($user)->authorize('update', $team);

        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
        ])->validateWithBag('updateTeamName');

        $team->forceFill([
            'name' => $input['name'],
        ])->save();
    }
}
