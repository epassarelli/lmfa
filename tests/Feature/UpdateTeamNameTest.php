<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Jetstream\Http\Livewire\UpdateTeamNameForm;
use Livewire\Livewire;
use Tests\TestCase;

class UpdateTeamNameTest extends TestCase
{
    use RefreshDatabase;

<<<<<<< HEAD
    public function test_team_names_can_be_updated()
=======
    public function test_team_names_can_be_updated(): void
>>>>>>> dev
    {
        $this->actingAs($user = User::factory()->withPersonalTeam()->create());

        Livewire::test(UpdateTeamNameForm::class, ['team' => $user->currentTeam])
<<<<<<< HEAD
                    ->set(['state' => ['name' => 'Test Team']])
                    ->call('updateTeamName');
=======
            ->set(['state' => ['name' => 'Test Team']])
            ->call('updateTeamName');
>>>>>>> dev

        $this->assertCount(1, $user->fresh()->ownedTeams);
        $this->assertEquals('Test Team', $user->currentTeam->fresh()->name);
    }
}
