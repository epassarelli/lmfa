<?php

namespace Tests\Feature\Pasarela;

use App\Models\Event;
use App\Models\Organization;
use App\Models\OrganizationMember;
use App\Models\PublicationRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * PC-10-HU-01: Tests del dashboard del publicador.
 */
class DashboardPublicadorTest extends TestCase
{
    use DatabaseTransactions;

    private function makeUserWithOrg(): array
    {
        $user = User::factory()->create();
        $org  = Organization::create([
            'type' => 'productora',
            'name' => 'Dash Org ' . uniqid(),
            'slug' => 'dash-org-' . uniqid(),
        ]);
        OrganizationMember::create([
            'organization_id' => $org->id,
            'user_id'         => $user->id,
            'role'            => 'owner',
            'status'          => 'active',
        ]);
        return [$user, $org];
    }

    public function test_guest_is_redirected(): void
    {
        $this->get(route('pasarela.dashboard'))
            ->assertRedirect(route('login'));
    }

    public function test_authenticated_user_sees_dashboard(): void
    {
        [$user] = $this->makeUserWithOrg();

        $this->actingAs($user)
            ->get(route('pasarela.dashboard'))
            ->assertOk()
            ->assertViewIs('pasarela.dashboard_publicador')
            ->assertSee('Dashboard');
    }

    public function test_dashboard_shows_request_counts(): void
    {
        [$user, $org] = $this->makeUserWithOrg();

        $event = Event::create([
            'organization_id'  => $org->id,
            'title'            => 'Dash Event ' . uniqid(),
            'slug'             => 'dash-event-' . uniqid(),
            'editorial_status' => 'approved',
            'status'           => 'active',
            'start_at'         => now()->addDays(5),
        ]);

        PublicationRequest::create([
            'content_type'         => Event::class,
            'content_id'           => $event->id,
            'requested_by'         => $user->id,
            'mode'                 => 'portal_only',
            'wants_portal_publish' => true,
            'wants_portal_social'  => false,
            'wants_own_social'     => false,
            'status'               => 'pending',
        ]);

        $this->actingAs($user)
            ->get(route('pasarela.dashboard'))
            ->assertOk()
            ->assertViewHas('totalRequests', 1)
            ->assertViewHas('recentRequests');
    }

    public function test_dashboard_only_shows_own_data(): void
    {
        [$user]      = $this->makeUserWithOrg();
        [$otherUser] = $this->makeUserWithOrg();

        $org2 = Organization::whereHas('members', fn($q) => $q->where('user_id', $otherUser->id))->first();
        $event = Event::create([
            'organization_id'  => $org2->id,
            'title'            => 'Other Event',
            'slug'             => 'other-event-' . uniqid(),
            'editorial_status' => 'approved',
            'status'           => 'active',
            'start_at'         => now()->addDays(3),
        ]);
        PublicationRequest::create([
            'content_type'         => Event::class,
            'content_id'           => $event->id,
            'requested_by'         => $otherUser->id,
            'mode'                 => 'portal_only',
            'wants_portal_publish' => true,
            'wants_portal_social'  => false,
            'wants_own_social'     => false,
            'status'               => 'pending',
        ]);

        // user should see 0 requests
        $this->actingAs($user)
            ->get(route('pasarela.dashboard'))
            ->assertOk()
            ->assertViewHas('totalRequests', 0);
    }
}
