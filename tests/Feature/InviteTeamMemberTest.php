<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Laravel\Jetstream\Features;
use Laravel\Jetstream\Http\Livewire\TeamMemberManager;
use Laravel\Jetstream\Mail\TeamInvitation;
use Livewire\Livewire;
use Tests\TestCase;

class InviteTeamMemberTest extends TestCase
{
    use RefreshDatabase;

<<<<<<< HEAD
    public function test_team_members_can_be_invited_to_team()
    {
        if (! Features::sendsTeamInvitations()) {
            return $this->markTestSkipped('Team invitations not enabled.');
=======
    public function test_team_members_can_be_invited_to_team(): void
    {
        if (! Features::sendsTeamInvitations()) {
            $this->markTestSkipped('Team invitations not enabled.');
>>>>>>> dev
        }

        Mail::fake();

        $this->actingAs($user = User::factory()->withPersonalTeam()->create());

        $component = Livewire::test(TeamMemberManager::class, ['team' => $user->currentTeam])
<<<<<<< HEAD
                        ->set('addTeamMemberForm', [
                            'email' => 'test@example.com',
                            'role' => 'admin',
                        ])->call('addTeamMember');
=======
            ->set('addTeamMemberForm', [
                'email' => 'test@example.com',
                'role' => 'admin',
            ])->call('addTeamMember');
>>>>>>> dev

        Mail::assertSent(TeamInvitation::class);

        $this->assertCount(1, $user->currentTeam->fresh()->teamInvitations);
    }

<<<<<<< HEAD
    public function test_team_member_invitations_can_be_cancelled()
    {
        if (! Features::sendsTeamInvitations()) {
            return $this->markTestSkipped('Team invitations not enabled.');
=======
    public function test_team_member_invitations_can_be_cancelled(): void
    {
        if (! Features::sendsTeamInvitations()) {
            $this->markTestSkipped('Team invitations not enabled.');
>>>>>>> dev
        }

        Mail::fake();

        $this->actingAs($user = User::factory()->withPersonalTeam()->create());

        // Add the team member...
        $component = Livewire::test(TeamMemberManager::class, ['team' => $user->currentTeam])
<<<<<<< HEAD
                        ->set('addTeamMemberForm', [
                            'email' => 'test@example.com',
                            'role' => 'admin',
                        ])->call('addTeamMember');
=======
            ->set('addTeamMemberForm', [
                'email' => 'test@example.com',
                'role' => 'admin',
            ])->call('addTeamMember');
>>>>>>> dev

        $invitationId = $user->currentTeam->fresh()->teamInvitations->first()->id;

        // Cancel the team invitation...
        $component->call('cancelTeamInvitation', $invitationId);

        $this->assertCount(0, $user->currentTeam->fresh()->teamInvitations);
    }
}
