<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Jetstream\Http\Livewire\TeamMemberManager;
use Livewire\Livewire;
use Tests\TestCase;

class RemoveTeamMemberTest extends TestCase
{
    use RefreshDatabase;

<<<<<<< HEAD
    public function test_team_members_can_be_removed_from_teams()
=======
    public function test_team_members_can_be_removed_from_teams(): void
>>>>>>> dev
    {
        $this->actingAs($user = User::factory()->withPersonalTeam()->create());

        $user->currentTeam->users()->attach(
            $otherUser = User::factory()->create(), ['role' => 'admin']
        );

        $component = Livewire::test(TeamMemberManager::class, ['team' => $user->currentTeam])
<<<<<<< HEAD
                        ->set('teamMemberIdBeingRemoved', $otherUser->id)
                        ->call('removeTeamMember');
=======
            ->set('teamMemberIdBeingRemoved', $otherUser->id)
            ->call('removeTeamMember');
>>>>>>> dev

        $this->assertCount(0, $user->currentTeam->fresh()->users);
    }

<<<<<<< HEAD
    public function test_only_team_owner_can_remove_team_members()
=======
    public function test_only_team_owner_can_remove_team_members(): void
>>>>>>> dev
    {
        $user = User::factory()->withPersonalTeam()->create();

        $user->currentTeam->users()->attach(
            $otherUser = User::factory()->create(), ['role' => 'admin']
        );

        $this->actingAs($otherUser);

        $component = Livewire::test(TeamMemberManager::class, ['team' => $user->currentTeam])
<<<<<<< HEAD
                        ->set('teamMemberIdBeingRemoved', $user->id)
                        ->call('removeTeamMember')
                        ->assertStatus(403);
=======
            ->set('teamMemberIdBeingRemoved', $user->id)
            ->call('removeTeamMember')
            ->assertStatus(403);
>>>>>>> dev
    }
}
