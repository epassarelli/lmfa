<?php

namespace Tests\Feature\Events;

use App\Models\Event;
use App\Models\Venue;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class EventVenueRelationTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function event_can_resolve_its_venue_model(): void
    {
        $venue = Venue::create([
            'name' => 'Centro Cultural del Norte',
            'slug' => 'centro-cultural-del-norte',
            'city' => 'Salta',
            'status' => 'active',
        ]);

        $event = Event::create([
            'venue_id' => $venue->id,
            'title' => 'Encuentro folklórico',
            'slug' => 'encuentro-folklorico-venue',
            'start_at' => now()->addDay(),
            'editorial_status' => 'published',
            'status' => 'active',
        ]);

        $this->assertTrue($event->venue->is($venue));
        $this->assertTrue($venue->events->contains($event));
    }
}
