<?php

namespace App\Actions\Jetstream;

<<<<<<< HEAD
=======
use App\Models\Team;
>>>>>>> dev
use Laravel\Jetstream\Contracts\DeletesTeams;

class DeleteTeam implements DeletesTeams
{
    /**
     * Delete the given team.
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
    {
        $team->purge();
    }
}
