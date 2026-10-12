<?php

namespace Tests\Feature\Events;

use App\Models\Provincia;
use App\Models\User;
use App\Models\Venue;
use App\Services\EventService;
use App\Services\VenueService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class EventVenuesTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        config(['responsecache.enabled' => false, 'responsecache.cache_store' => 'array']);
        Http::fake();
        Queue::fake();
    }

    private function user(string $role = 'administrador'): User
    {
        Role::findOrCreate($role, 'web');
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }

    private function place(array $overrides = []): array
    {
        return array_merge(['name' => 'Anfiteatro de prueba '.uniqid(), 'address' => 'Calle Ficticia 123', 'city' => 'Localidad de prueba', 'province_id' => (Provincia::first() ?: Provincia::create(['nombre' => 'Provincia de prueba']))->id,
            'latitude' => -31.42, 'longitude' => -64.18, 'status' => 'active'], $overrides);
    }

    public function test_admin_abm_preserves_slug_and_propagates_location_to_linked_events(): void
    {
        $this->actingAs($this->user());
        $input = $this->place();
        $this->post(route('backend.venues.store'), $input)->assertSessionHasNoErrors()->assertRedirect();
        $venue = Venue::where('name', $input['name'])->firstOrFail();
        $slug = $venue->slug;
        $event = app(EventService::class)->createEvent(['title' => 'Evento en sala '.uniqid(), 'start_at' => now()->addDay(), 'body' => '<p>Prueba</p>', 'venue_id' => $venue->id, 'city' => 'No debe prevalecer', 'editorial_status' => 'published']);
        $this->assertSame($venue->city, $event->city);
        $this->put(route('backend.venues.update', $venue), array_merge($input, ['name' => 'Nombre corregido '.uniqid(), 'address' => 'Calle Corregida 456']))->assertSessionHasNoErrors();
        $this->assertSame($slug, $venue->fresh()->slug);
        $this->assertSame('Calle Corregida 456', $event->fresh()->address);
        $this->get(route('cartelera.show', $event->slug))->assertOk()->assertSee('Calle Corregida 456')->assertSee($venue->fresh()->name);
    }

    public function test_used_place_cannot_be_deleted_but_can_be_inactivated(): void
    {
        $this->actingAs($this->user());
        $venue = app(VenueService::class)->save($this->place());
        $event = app(EventService::class)->createEvent(['title' => 'Prueba de vínculo', 'start_at' => now()->addDay(), 'venue_id' => $venue->id]);
        $this->delete(route('backend.venues.destroy', $venue))->assertSessionHas('error');
        $this->assertNotNull($venue->fresh());
        $this->put(route('backend.venues.update', $venue), array_merge($venue->only(['name', 'address', 'city', 'province_id']), ['status' => 'inactive']))->assertSessionHasNoErrors();
        $this->assertSame($venue->id, $event->fresh()->venue_id);
        $this->assertSame('inactive', $venue->fresh()->status);
    }

    public function test_unused_place_can_be_deleted_and_invalid_coordinates_and_duplicates_are_rejected(): void
    {
        $this->actingAs($this->user());
        $input = $this->place();
        $venue = app(VenueService::class)->save($input);
        $this->post(route('backend.venues.store'), $input)->assertSessionHasErrors('name');
        $this->post(route('backend.venues.store'), $this->place(['latitude' => 120]))->assertSessionHasErrors('latitude');
        $this->post(route('backend.venues.store'), $this->place(['longitude' => null]))->assertSessionHasErrors('longitude');
        $this->delete(route('backend.venues.destroy', $venue))->assertSessionHas('success');
        $this->assertNull($venue->fresh());
    }

    public function test_collaborator_proposes_pending_place_and_cannot_access_catalog_abm(): void
    {
        $this->actingAs($this->user('colaborador'));
        $input = $this->place();
        $this->get(route('backend.venues.index'))->assertForbidden();
        $this->post(route('backend.venues.store'), $input)->assertForbidden();
        $this->post(route('backend.events.store'), ['title' => 'Propuesta con lugar '.uniqid(), 'body' => '<p>Detalle</p>', 'start_at' => now()->addDay()->toDateTimeString(), 'estado' => 1, 'new_venue' => $input])->assertSessionHasNoErrors();
        $venue = Venue::where('name', $input['name'])->firstOrFail();
        $this->assertSame('pending', $venue->status);
        $event = $venue->events()->firstOrFail();
        $this->assertSame('draft', $event->editorial_status);
        $this->actingAs($this->user());
        $this->post(route('backend.moderation.publish', ['type' => 'event', 'id' => $event->id]))->assertSessionHasErrors('venue_id');
        $this->put(route('backend.venues.update', $venue), array_merge($input, ['status' => 'active']))->assertSessionHasNoErrors();
        $this->post(route('backend.moderation.publish', ['type' => 'event', 'id' => $event->id]))->assertRedirect();
        $this->assertSame('published', $event->fresh()->editorial_status);
    }

    public function test_legacy_location_without_venue_is_preserved_on_partial_update(): void
    {
        $this->actingAs($this->user());
        $event = app(EventService::class)->createEvent(['title' => 'Evento legacy '.uniqid(), 'start_at' => now()->addDay(), 'city' => 'Localidad anterior', 'address' => 'Dirección anterior']);
        app(EventService::class)->updateEvent($event, ['title' => 'Título corregido']);
        $this->assertSame('Localidad anterior', $event->fresh()->city);
        $this->assertSame('Dirección anterior', $event->fresh()->address);
        $this->assertNull($event->fresh()->venue_id);
    }
}
