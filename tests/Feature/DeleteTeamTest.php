<?php

namespace Tests\Feature;

use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Jetstream\Http\Livewire\DeleteTeamForm;
use Livewire\Livewire;
use Tests\TestCase;

class DeleteTeamTest extends TestCase
{
    use RefreshDatabase;

<<<<<<< HEAD
    public function test_teams_can_be_deleted()
=======
    public function test_teams_can_be_deleted(): void
>>>>>>> dev
    {
        $this->actingAs($user = User::factory()->withPersonalTeam()->create());

        $user->ownedTeams()->save($team = Team::factory()->make([
            'personal_team' => false,
        ]));

        $team->users()->attach(
            $otherUser = User::factory()->create(), ['role' => 'test-role']
        );

        $component = Livewire::test(DeleteTeamForm::class, ['team' => $team->fresh()])
<<<<<<< HEAD
                                ->call('deleteTeam');
=======
            ->call('deleteTeam');
>>>>>>> dev

        $this->assertNull($team->fresh());
        $this->assertCount(0, $otherUser->fresh()->teams);
    }

<<<<<<< HEAD
    public function test_personal_teams_cant_be_deleted()
=======
    public function test_personal_teams_cant_be_deleted(): void
>>>>>>> dev
    {
        $this->actingAs($user = User::factory()->withPersonalTeam()->create());

        $component = Livewire::test(DeleteTeamForm::class, ['team' => $user->currentTeam])
<<<<<<< HEAD
                                ->call('deleteTeam')
                                ->assertHasErrors(['team']);
=======
            ->call('deleteTeam')
            ->assertHasErrors(['team']);
>>>>>>> dev

        $this->assertNotNull($user->currentTeam->fresh());
    }
}
