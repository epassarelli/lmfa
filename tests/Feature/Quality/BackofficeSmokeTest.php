<?php

namespace Tests\Feature\Quality;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class BackofficeSmokeTest extends TestCase
{
    use DatabaseTransactions;

    public function test_main_backoffice_screens_render_for_administrator(): void
    {
        Role::findOrCreate('administrador', 'web');
        $admin = User::factory()->create();
        $admin->assignRole('administrador');
        $this->actingAs($admin);

        foreach ([
            'backend.events.index', 'backend.events.create',
            'backend.venues.index', 'backend.venues.create',
            'backend.festivales.index', 'backend.festivales.create',
            'backend.organizations.index', 'backend.organizations.create',
            'backend.claims.index', 'backend.my-entities.index',
            'backend.moderation.index',
        ] as $name) {
            $this->get(route($name))->assertOk();
        }
    }

    public function test_backoffice_screens_require_authentication(): void
    {
        foreach (['backend.events.index', 'backend.organizations.index', 'backend.claims.index'] as $name) {
            $this->get(route($name))->assertRedirect();
        }
    }
}
