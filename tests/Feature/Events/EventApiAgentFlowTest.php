<?php

namespace Tests\Feature\Events;

use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class EventApiAgentFlowTest extends TestCase
{
    use DatabaseTransactions;

    protected function makeAdminUser(): User
    {
        Role::findOrCreate('administrador', 'web');

        $user = User::factory()->create();
        $user->assignRole('administrador');

        return $user;
    }

    /** @test */
    public function admin_api_create_event_defaults_to_draft_when_editorial_status_is_omitted(): void
    {
        $admin = $this->makeAdminUser();
        Sanctum::actingAs($admin);

        $response = $this->postJson('/api/v1/events', [
            'title' => 'Peña en Cordoba',
            'body' => 'Encuentro folklorico con artistas invitados.',
            'start_at' => now()->addDay()->toDateTimeString(),
            'city' => 'Cordoba',
            'event_type' => 'pena',
        ]);

        $response->assertCreated()
            ->assertJsonPath('editorial_status', 'draft');

        $event = Event::where('title', 'Peña en Cordoba')->firstOrFail();

        $this->assertSame('draft', $event->editorial_status);
        $this->assertNull($event->published_at);
    }

    /** @test */
    public function admin_api_create_event_accepts_explicit_draft_status(): void
    {
        $admin = $this->makeAdminUser();
        Sanctum::actingAs($admin);

        $response = $this->postJson('/api/v1/events', [
            'title' => 'Festival barrial',
            'body' => 'Descripcion del festival.',
            'start_at' => now()->addDays(2)->toDateTimeString(),
            'editorial_status' => 'draft',
        ]);

        $response->assertCreated()
            ->assertJsonPath('editorial_status', 'draft');
    }

    /** @test */
    public function non_admin_cannot_create_events_via_api(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/events', [
            'title' => 'Evento bloqueado',
            'start_at' => now()->addDay()->toDateTimeString(),
        ]);

        $response->assertForbidden();
    }
}
