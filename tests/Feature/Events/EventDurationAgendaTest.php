<?php

namespace Tests\Feature\Events;

use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class EventDurationAgendaTest extends TestCase
{
    use DatabaseTransactions;

    public function test_ongoing_event_appears_once_on_today_and_specific_date_and_month(): void
    {
        config(['responsecache.enabled' => false, 'responsecache.cache_store' => 'array']);
        $event = Event::create(['title' => 'Continuo de prueba '.uniqid(), 'slug' => 'continuo-'.uniqid(), 'body' => '<p>Prueba</p>',
            'start_at' => now()->subDays(2)->setTime(20, 0), 'end_at' => now()->addDay()->setTime(23, 0), 'status' => 'active',
            'editorial_status' => 'published', 'published_at' => now()->subWeek(), 'created_by' => User::factory()->create()->id]);
        foreach ([['today' => '1'], ['fecha' => today()->toDateString()], ['mes' => today()->format('Y-m')]] as $filters) {
            $this->get(route('cartelera.index', array_merge($filters, ['q' => $event->title])))->assertOk()->assertViewHas('shows', fn ($shows) => $shows->total() === 1 && $shows->first()->id === $event->id);
        }
    }

    public function test_agenda_excludes_draft_scheduled_and_cancelled_events(): void
    {
        config(['responsecache.enabled' => false, 'responsecache.cache_store' => 'array']);
        $owner = User::factory()->create();
        foreach ([['editorial_status' => 'draft'], ['published_at' => now()->addDay()], ['status' => 'cancelled']] as $overrides) {
            Event::create(array_merge(['title' => 'Oculto de agenda '.uniqid(), 'slug' => 'oculto-'.uniqid(), 'start_at' => now()->addWeek(), 'status' => 'active', 'editorial_status' => 'published', 'created_by' => $owner->id], $overrides));
        }
        $this->get(route('cartelera.index', ['q' => 'Oculto de agenda']))->assertOk()->assertViewHas('shows', fn ($shows) => $shows->total() === 0);
    }
}
