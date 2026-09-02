<?php

namespace Tests\Feature\Festivals;

use App\Models\Festival;
use App\Models\Event;
use App\Models\Interprete;
use Database\Seeders\FestivalJourneyDemoSeeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class FestivalJourneyFrontendTest extends TestCase
{
    use DatabaseTransactions;

    public function test_the_festival_page_keeps_journey_modules_hidden_when_disabled(): void
    {
        $this->seed(FestivalJourneyDemoSeeder::class);
        $festival = Festival::where('slug', 'encuentro-demo-del-litoral')->firstOrFail();

        config()->set('features.festival_journey', false);
        config()->set('features.festival_journey_allowlist', [$festival->id]);

        $this->get(route('festivales.show', $festival->slug))
            ->assertOk()
            ->assertDontSee('Próximas fechas')
            ->assertDontSee('data-journey-list', false);
    }

    public function test_the_enabled_festival_page_renders_canonical_journey_links_and_metadata(): void
    {
        $this->seed(FestivalJourneyDemoSeeder::class);
        $festival = Festival::where('slug', 'encuentro-demo-del-litoral')->firstOrFail();

        config()->set('features.festival_journey', true);
        config()->set('features.festival_journey_allowlist', [$festival->id]);

        $this->get(route('festivales.show', $festival->slug))
            ->assertOk()
            ->assertSee('Proximas fechas')
            ->assertSee('data-journey-list', false)
            ->assertSee('data-journey-link', false)
            ->assertSee('data-module="upcoming_events"', false)
            ->assertSee(route('cartelera.show', 'evento-demo-festival-vivo-1'), false);
    }

    public function test_event_and_artist_pages_render_journey_modules_only_for_the_pilot_content(): void
    {
        $this->seed(FestivalJourneyDemoSeeder::class);
        $festival = Festival::where('slug', 'encuentro-demo-del-litoral')->firstOrFail();
        $event = Event::where('slug', 'evento-demo-festival-vivo-1')->firstOrFail();
        $artist = Interprete::where('slug', 'artista-demo-festival-vivo-1')->firstOrFail();

        config()->set('features.festival_journey', true);
        config()->set('features.festival_journey_allowlist', [$festival->id]);

        $this->get(route('cartelera.show', $event->slug))
            ->assertOk()
            ->assertSee('Este evento forma parte de')
            ->assertSee('data-module="event_festivals"', false);

        $this->get(route('artista.show', $artist->slug))
            ->assertOk()
            ->assertSee('Festivales relacionados')
            ->assertSee('data-module="artist_festivals"', false)
            ->assertSee('Próximas fechas en festivales vinculados');
    }
}
