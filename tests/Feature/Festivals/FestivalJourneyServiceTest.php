<?php

namespace Tests\Feature\Festivals;

use App\Models\Festival;
use App\Models\Event;
use App\Models\Interprete;
use App\Services\Product\FestivalJourneyService;
use Database\Seeders\FestivalJourneyDemoSeeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class FestivalJourneyServiceTest extends TestCase
{
    use DatabaseTransactions;

    public function test_it_keeps_the_journey_hidden_when_the_flag_is_disabled(): void
    {
        $this->seed(FestivalJourneyDemoSeeder::class);
        $festival = Festival::where('slug', 'encuentro-demo-del-litoral')->firstOrFail();

        config()->set('features.festival_journey', false);
        config()->set('features.festival_journey_allowlist', [$festival->id]);

        $journey = app(FestivalJourneyService::class)->forFestival($festival);

        $this->assertFalse($journey->enabled);
        $this->assertCount(0, $journey->upcomingEvents);
    }

    public function test_it_returns_only_allowlisted_visible_related_content(): void
    {
        $this->seed(FestivalJourneyDemoSeeder::class);
        $festival = Festival::where('slug', 'encuentro-demo-del-litoral')->firstOrFail();

        config()->set('features.festival_journey', true);
        config()->set('features.festival_journey_allowlist', [$festival->id]);

        $journey = app(FestivalJourneyService::class)->forFestival($festival);

        $this->assertTrue($journey->enabled);
        $this->assertCount(1, $journey->upcomingEvents);
        $this->assertSame('evento-demo-festival-vivo-1', $journey->upcomingEvents->first()->slug);
        $this->assertCount(1, $journey->artists);
        $this->assertSame('artista-demo-festival-vivo-1', $journey->artists->first()->slug);
    }

    public function test_it_orders_and_limits_upcoming_events_to_the_first_three(): void
    {
        $this->seed(FestivalJourneyDemoSeeder::class);
        $festival = Festival::where('slug', 'encuentro-demo-del-litoral')->firstOrFail();
        $artist = Interprete::where('slug', 'artista-demo-festival-vivo-1')->firstOrFail();

        foreach ([10, 11, 12, 13] as $days) {
            $event = Event::create([
                'title' => "Fecha ordenada {$days}",
                'slug' => "fecha-ordenada-{$days}",
                'body' => '<p>Evento de prueba.</p>',
                'start_at' => now()->addDays($days),
                'province_id' => $festival->province_id,
                'city' => 'Ciudad Demo',
                'status' => 'active',
                'editorial_status' => 'published',
                'published_at' => now()->subDay(),
                'created_by' => $festival->user_id,
            ]);
            $festival->events()->attach($event->id);
            $event->interpretes()->attach($artist->id, ['sort_order' => 1]);
        }

        config()->set('features.festival_journey', true);
        config()->set('features.festival_journey_allowlist', [$festival->id]);

        $journey = app(FestivalJourneyService::class)->forFestival($festival);

        $this->assertCount(3, $journey->upcomingEvents);
        $this->assertSame([
            'fecha-ordenada-10',
            'fecha-ordenada-11',
            'fecha-ordenada-12',
        ], $journey->upcomingEvents->pluck('slug')->all());
    }

    public function test_it_enables_event_and_artist_continuations_only_for_allowlisted_festivals(): void
    {
        $this->seed(FestivalJourneyDemoSeeder::class);
        $festival = Festival::where('slug', 'encuentro-demo-del-litoral')->firstOrFail();
        $event = Event::where('slug', 'evento-demo-festival-vivo-1')->firstOrFail();
        $artist = Interprete::where('slug', 'artista-demo-festival-vivo-1')->firstOrFail();

        config()->set('features.festival_journey', true);
        config()->set('features.festival_journey_allowlist', [$festival->id]);

        $eventJourney = app(FestivalJourneyService::class)->forEvent($event);
        $artistJourney = app(FestivalJourneyService::class)->forArtist($artist);

        $this->assertTrue($eventJourney['enabled']);
        $this->assertSame([$festival->id], $eventJourney['festivals']->pluck('id')->all());
        $this->assertTrue($artistJourney['enabled']);
        $this->assertSame([$festival->id], $artistJourney['festivals']->pluck('id')->all());
        $this->assertSame([$event->id], $artistJourney['events']->pluck('id')->all());
    }
}
