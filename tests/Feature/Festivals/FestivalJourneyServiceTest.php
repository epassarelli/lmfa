<?php

namespace Tests\Feature\Festivals;

use App\Models\Festival;
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
}
