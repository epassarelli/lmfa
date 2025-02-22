<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Jetstream\Http\Livewire\CreateTeamForm;
use Livewire\Livewire;
use Tests\TestCase;

class CreateTeamTest extends TestCase
{
    use RefreshDatabase;

<<<<<<< HEAD
    public function test_teams_can_be_created()
=======
    public function test_teams_can_be_created(): void
>>>>>>> dev
    {
        $this->actingAs($user = User::factory()->withPersonalTeam()->create());

        Livewire::test(CreateTeamForm::class)
<<<<<<< HEAD
                    ->set(['state' => ['name' => 'Test Team']])
                    ->call('createTeam');
=======
            ->set(['state' => ['name' => 'Test Team']])
            ->call('createTeam');
>>>>>>> dev

        $this->assertCount(2, $user->fresh()->ownedTeams);
        $this->assertEquals('Test Team', $user->fresh()->ownedTeams()->latest('id')->first()->name);
    }
}
