<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Jetstream\Http\Livewire\TeamMemberManager;
use Livewire\Livewire;
use Tests\TestCase;

class LeaveTeamTest extends TestCase
{
    use RefreshDatabase;

<<<<<<< HEAD
    public function test_users_can_leave_teams()
=======
    public function test_users_can_leave_teams(): void
>>>>>>> dev
    {
        $user = User::factory()->withPersonalTeam()->create();

        $user->currentTeam->users()->attach(
            $otherUser = User::factory()->create(), ['role' => 'admin']
        );

        $this->actingAs($otherUser);

        $component = Livewire::test(TeamMemberManager::class, ['team' => $user->currentTeam])
<<<<<<< HEAD
                        ->call('leaveTeam');
=======
            ->call('leaveTeam');
>>>>>>> dev

        $this->assertCount(0, $user->currentTeam->fresh()->users);
    }

<<<<<<< HEAD
    public function test_team_owners_cant_leave_their_own_team()
=======
    public function test_team_owners_cant_leave_their_own_team(): void
>>>>>>> dev
    {
        $this->actingAs($user = User::factory()->withPersonalTeam()->create());

        $component = Livewire::test(TeamMemberManager::class, ['team' => $user->currentTeam])
<<<<<<< HEAD
                        ->call('leaveTeam')
                        ->assertHasErrors(['team']);
=======
            ->call('leaveTeam')
            ->assertHasErrors(['team']);
>>>>>>> dev

        $this->assertNotNull($user->currentTeam->fresh());
    }
}
