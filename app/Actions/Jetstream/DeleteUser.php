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
use Illuminate\Support\Facades\DB;
use Laravel\Jetstream\Contracts\DeletesTeams;
use Laravel\Jetstream\Contracts\DeletesUsers;

class DeleteUser implements DeletesUsers
{
    /**
     * The team deleter implementation.
     *
     * @var \Laravel\Jetstream\Contracts\DeletesTeams
     */
    protected $deletesTeams;

    /**
     * Create a new action instance.
<<<<<<< HEAD
=======
<<<<<<< HEAD
     *
     * @param  \Laravel\Jetstream\Contracts\DeletesTeams  $deletesTeams
     * @return void
=======
>>>>>>> dev
>>>>>>> f6a1528eb1fc36098adb0d1c90a2861b3666f2d7
     */
    public function __construct(DeletesTeams $deletesTeams)
    {
        $this->deletesTeams = $deletesTeams;
    }

    /**
     * Delete the given user.
<<<<<<< HEAD
     */
    public function delete(User $user): void
=======
<<<<<<< HEAD
     *
     * @param  mixed  $user
     * @return void
     */
    public function delete($user)
=======
     */
    public function delete(User $user): void
>>>>>>> dev
>>>>>>> f6a1528eb1fc36098adb0d1c90a2861b3666f2d7
    {
        DB::transaction(function () use ($user) {
            $this->deleteTeams($user);
            $user->deleteProfilePhoto();
            $user->tokens->each->delete();
            $user->delete();
        });
    }

    /**
     * Delete the teams and team associations attached to the user.
<<<<<<< HEAD
=======
<<<<<<< HEAD
     *
     * @param  mixed  $user
     * @return void
>>>>>>> f6a1528eb1fc36098adb0d1c90a2861b3666f2d7
     */
    protected function deleteTeams(User $user): void
    {
        $user->teams()->detach();

<<<<<<< HEAD
        $user->ownedTeams->each(function (Team $team) {
=======
        $user->ownedTeams->each(function ($team) {
=======
     */
    protected function deleteTeams(User $user): void
    {
        $user->teams()->detach();

        $user->ownedTeams->each(function (Team $team) {
>>>>>>> dev
>>>>>>> f6a1528eb1fc36098adb0d1c90a2861b3666f2d7
            $this->deletesTeams->delete($team);
        });
    }
}
