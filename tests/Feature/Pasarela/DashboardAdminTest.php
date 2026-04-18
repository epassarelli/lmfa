<?php

namespace Tests\Feature\Pasarela;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * PC-11-HU-01: Tests básicos del dashboard administrativo.
 */
class DashboardAdminTest extends TestCase
{
    use DatabaseTransactions;

    public function test_guest_is_redirected(): void
    {
        $this->get(route('pasarela.admin.dashboard'))
            ->assertRedirect(route('login'));
    }

    public function test_authenticated_user_sees_admin_dashboard(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('pasarela.admin.dashboard'))
            ->assertOk()
            ->assertViewIs('pasarela.dashboard_admin')
            ->assertViewHas('pendingModeration')
            ->assertViewHas('publishedToday')
            ->assertViewHas('failuresByProvider')
            ->assertViewHas('expiredTokens')
            ->assertViewHas('recentFailures')
            ->assertViewHas('topPublishers')
            ->assertViewHas('targetsToday');
    }
}
