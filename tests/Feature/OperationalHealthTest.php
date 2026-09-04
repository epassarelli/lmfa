<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\OperationalHealthService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class OperationalHealthTest extends TestCase
{
    use DatabaseTransactions;

    public function test_public_healthcheck_confirms_application_and_database_availability(): void
    {
        $this->getJson(route('healthz'))
            ->assertOk()
            ->assertExactJson(['status' => 'ok', 'checks' => ['database' => 'ok']]);
    }

    public function test_non_administrators_cannot_open_operational_diagnosis(): void
    {
        $this->actingAs(User::factory()->create())
            ->getJson(route('backend.operational-health'))
            ->assertForbidden();
    }

    public function test_administrator_receives_sanitized_operational_diagnosis(): void
    {
        Role::findOrCreate('administrador', 'web');
        $admin = User::factory()->create();
        $admin->assignRole('administrador');
        app(OperationalHealthService::class)->recordSchedulerHeartbeat();

        $response = $this->actingAs($admin)->getJson(route('backend.operational-health'));

        $response->assertJsonStructure([
            'status',
            'checks' => ['database' => ['status'], 'cache' => ['status'], 'scheduler' => ['status', 'age_seconds'], 'queue' => ['status', 'connection']],
        ])->assertJsonMissingPath('exception')->assertJsonMissingPath('trace');
    }

    public function test_scheduler_diagnosis_is_degraded_when_heartbeat_is_missing(): void
    {
        Cache::forget('operations:scheduler_heartbeat');

        $diagnosis = app(OperationalHealthService::class)->diagnosis();

        $this->assertSame('degraded', $diagnosis['checks']['scheduler']['status']);
    }
}
