<?php

namespace App\Actions\Jetstream;

<<<<<<< HEAD
use App\Models\Team;
=======
<<<<<<< HEAD
=======
use App\Models\Team;
>>>>>>> dev
>>>>>>> f6a1528eb1fc36098adb0d1c90a2861b3666f2d7
use Laravel\Jetstream\Contracts\DeletesTeams;

class DeleteTeam implements DeletesTeams
{
    /**
     * Delete the given team.
<<<<<<< HEAD
     */
    public function delete(Team $team): void
=======
<<<<<<< HEAD
     *
     * @param  mixed  $team
     * @return void
     */
    public function delete($team)
=======
     */
    public function delete(Team $team): void
>>>>>>> dev
>>>>>>> f6a1528eb1fc36098adb0d1c90a2861b3666f2d7
    {
        $team->purge();
    }
}
