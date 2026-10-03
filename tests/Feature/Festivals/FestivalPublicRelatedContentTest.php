<?php

namespace Tests\Feature\Festivals;

use App\Models\Event;
use App\Models\Festival;
use App\Models\Interprete;
use App\Models\KnowledgeArticle;
use App\Models\KnowledgeCategory;
use App\Models\Mes;
use App\Models\News;
use App\Models\Provincia;
use App\Models\User;
use App\Services\Product\FestivalJourneyService;
use App\Support\CanonicalUrl;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class FestivalPublicRelatedContentTest extends TestCase
{
    use DatabaseTransactions;

    private Festival $festival;
    private User $author;

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo(now()->setTime(12, 0));
        config()->set('features.festival_journey', false);
        config()->set('features.festival_journey_allowlist', []);
        config()->set('responsecache.enabled', false);
        config()->set('responsecache.cache_store', 'array');

        $this->author = User::factory()->create();
        $province = Provincia::create(['nombre' => 'Provincia Prueba '.Str::uuid()]);
        $month = Mes::firstOrCreate(['nombre' => 'Enero']);
        $this->festival = Festival::create([
            'title' => 'Festival de prueba de relaciones',
            'slug' => 'festival-relaciones-'.Str::uuid(),
            'body' => '<p>Historia del festival.</p>',
            'status' => 'published',
            'published_at' => now()->subDay(),
            'province_id' => $province->id,
            'mes_id' => $month->id,
            'user_id' => $this->author->id,
        ]);
    }

    public function test_all_public_explicit_relations_render_without_pilot_configuration(): void
    {
        $event = $this->event('Noche uno del festival');
        $artist = $this->artist('Artista vinculado');
        $event->interpretes()->attach($artist->id, ['sort_order' => 1]);
        $news = $this->news('Noticia pública vinculada');
        $article = $this->article('Historia pública vinculada');

        Model::preventLazyLoading();
        try {
            $response = $this->get($this->url());
            $response->assertOk()
                ->assertSee('Próximas fechas')
                ->assertSee('Artistas vinculados al festival')
                ->assertSee('Historia y contexto')
                ->assertSee('Actualidad del festival')
                ->assertSee($event->title)
                ->assertSee('21:30 h')
                ->assertSee(route('cartelera.show', $event->slug), false)
                ->assertSee(route('artista.show', $artist->slug), false)
                ->assertSee(route('noticias.show', $news->slug), false)
                ->assertSee($article->getUrl(), false);
            $this->assertSame(1, substr_count($response->getContent(), '<h1'));
            $this->assertLessThan(350 * 1024, strlen($response->getContent()));
        } finally {
            Model::preventLazyLoading(false);
        }
    }

    public function test_ineligible_and_unlinked_content_never_creates_modules(): void
    {
        $this->event('Evento pasado', ['start_at' => now()->subDay()]);
        $this->event('Evento cancelado', ['status' => 'cancelled']);
        $this->event('Evento borrador', ['editorial_status' => 'draft']);
        $this->event('Evento con publicación futura', ['published_at' => now()->addDay()]);
        $this->artist('Artista inactivo', 0);
        $this->news('Noticia borrador', ['editorial_status' => 'draft']);
        $this->news('Noticia programada', ['published_at' => now()->addDay()]);
        $this->article('Artículo borrador', ['editorial_status' => 'draft']);
        $this->article('Artículo programado', ['published_at' => now()->addDay()]);
        $deleted = $this->article('Artículo eliminado');
        $deleted->delete();
        $unlinked = $this->event('Evento sin relación');
        $this->festival->events()->detach($unlinked->id);

        $this->get($this->url())->assertOk()
            ->assertDontSee('data-journey-list', false)
            ->assertDontSee('Próximas fechas')
            ->assertDontSee('Artistas vinculados al festival')
            ->assertDontSee('Historia y contexto')
            ->assertDontSee('Actualidad del festival')
            ->assertDontSee($unlinked->title);
    }

    public function test_every_upcoming_night_is_reachable_with_stable_order_and_canonical(): void
    {
        $events = collect();
        foreach ([5, 2, 4, 1, 3] as $day) {
            $events->push($this->event('Jornada '.$day, ['start_at' => now()->addDays($day)->setTime(21, 30)]));
        }
        $ordered = $events->sortBy('start_at')->values();
        $first = $this->get($this->url())->assertOk()
            ->assertSee($this->url().'?eventos_page=2#proximas-fechas', false)
            ->assertSee('<meta name="robots" content="index,follow">', false);
        $second = $this->get($this->url().'?eventos_page=2')->assertOk()
            ->assertSee('<meta name="robots" content="noindex,follow">', false)
            ->assertSee('<link rel="canonical" href="'.CanonicalUrl::normalize($this->url()).'"', false)
            ->assertSee('data-position="4"', false);
        foreach ($ordered as $index => $event) {
            ($index < 3 ? $first : $second)->assertSee($event->title);
            ($index < 3 ? $second : $first)->assertDontSee($event->title);
        }
        $this->assertSame(1, substr_count($second->getContent(), '<h1'));
        $journey = app(FestivalJourneyService::class)->forFestival($this->festival, 2);
        $this->assertSame($ordered->slice(3)->pluck('id')->values()->all(), $journey->upcomingEvents->pluck('id')->all());
        $this->assertSame(5, $journey->eventPagination->total());

        $this->get($this->url().'?eventos_page=99')->assertOk()
            ->assertSee('data-public-pagination', false)
            ->assertSee('?eventos_page=98#proximas-fechas', false);
    }

    public function test_empty_festival_keeps_related_province_and_month_navigation(): void
    {
        $related = Festival::create([
            'title' => 'Festival de la misma provincia',
            'slug' => 'festival-vecino-'.Str::uuid(),
            'body' => '<p>Otro festival.</p>',
            'status' => 'published',
            'province_id' => $this->festival->province_id,
            'user_id' => $this->author->id,
            'mes_id' => $this->festival->mes_id,
        ]);
        $this->get($this->url())->assertOk()
            ->assertDontSee('data-journey-list', false)
            ->assertSee('Festivales de la misma provincia')
            ->assertSee('Festivales del mismo mes')
            ->assertSee($related->getUrl(), false);
    }

    public function test_collections_are_bounded_and_queries_do_not_grow_per_item(): void
    {
        $this->event('Primera fecha');
        $this->artist('Primer artista');
        $this->news('Primera noticia');
        $this->article('Primer artículo');
        DB::enableQueryLog();
        try {
            DB::flushQueryLog();
            $small = app(FestivalJourneyService::class)->forFestival($this->festival);
            $smallQueries = count(DB::getQueryLog());
            foreach (range(1, 8) as $index) {
                $this->event('Fecha adicional '.$index);
                $this->artist('Artista adicional '.$index);
                $this->news('Noticia adicional '.$index);
                $this->article('Artículo adicional '.$index);
            }
            DB::flushQueryLog();
            $large = app(FestivalJourneyService::class)->forFestival($this->festival);
            $this->assertSame($smallQueries, count(DB::getQueryLog()));
            $this->assertCount(3, $large->upcomingEvents);
            $this->assertCount(6, $large->artists);
            $this->assertCount(3, $large->news);
            $this->assertCount(4, $large->knowledgeArticles);
            $this->assertCount(1, $small->upcomingEvents);
        } finally {
            DB::disableQueryLog();
        }
    }

    public function test_event_and_artist_continuations_still_require_the_pilot(): void
    {
        $event = $this->event('Evento fuera del piloto');
        $artist = $this->artist('Artista fuera del piloto');
        $event->interpretes()->attach($artist->id);
        $service = app(FestivalJourneyService::class);
        $this->assertFalse($service->forEvent($event)['enabled']);
        $this->assertFalse($service->forArtist($artist)['enabled']);
        config()->set('features.festival_journey', true);
        $this->assertFalse($service->forEvent($event)['enabled']);
        $this->assertEmpty($service->forArtist($artist)['festivals']);
        $this->assertTrue($service->forFestival($this->festival)->enabled);
    }

    public function test_today_remains_visible_and_equal_start_times_use_id_order(): void
    {
        $today = $this->event('Fecha de hoy', ['start_at' => now()->startOfDay()]);
        $first = $this->event('Primera fecha simultánea');
        $second = $this->event('Segunda fecha simultánea');
        $this->assertSame(
            [$today->id, $first->id, $second->id],
            app(FestivalJourneyService::class)->forFestival($this->festival)->upcomingEvents->pluck('id')->all()
        );
        $this->festival->status = 'draft';
        $this->assertFalse(app(FestivalJourneyService::class)->forFestival($this->festival)->enabled);
        $this->festival->status = 'published';
        $this->festival->published_at = now()->addDay();
        $this->assertFalse(app(FestivalJourneyService::class)->forFestival($this->festival)->enabled);
    }

    private function url(): string
    {
        return route('festivales.show', $this->festival->slug);
    }

    private function event(string $title, array $overrides = []): Event
    {
        $event = Event::create(array_merge([
            'title' => $title,
            'slug' => 'fecha-prueba-'.Str::uuid(),
            'body' => '<p>Jornada de prueba.</p>',
            'start_at' => now()->addDay()->setTime(21, 30),
            'status' => 'active',
            'editorial_status' => 'published',
            'published_at' => now()->subDay(),
            'province_id' => $this->festival->province_id,
            'city' => 'Ciudad de prueba',
            'created_by' => $this->author->id,
        ], $overrides));
        $this->festival->events()->attach($event->id);
        return $event;
    }

    private function artist(string $name, int $active = 1): Interprete
    {
        $artist = Interprete::create([
            'interprete' => $name,
            'slug' => 'artista-prueba-'.Str::uuid(),
            'biografia' => '<p>Biografía de prueba.</p>',
            'estado' => $active,
            'user_id' => $this->author->id,
        ]);
        $this->festival->interpretes()->attach($artist->id);
        return $artist;
    }

    private function news(string $title, array $overrides = []): News
    {
        $news = News::create(array_merge([
            'title' => $title,
            'slug' => 'noticia-prueba-'.Str::uuid(),
            'body' => '<p>Noticia de prueba.</p>',
            'editorial_status' => 'published',
            'published_at' => now()->subDay(),
            'created_by' => $this->author->id,
        ], $overrides));
        $this->festival->noticias()->attach($news->id);
        return $news;
    }

    private function article(string $title, array $overrides = []): KnowledgeArticle
    {
        $category = KnowledgeCategory::factory()->create(['slug' => 'contexto-prueba-'.Str::uuid()]);
        $article = KnowledgeArticle::factory()->published()->create(array_merge([
            'title' => $title,
            'slug' => 'contexto-prueba-'.Str::uuid(),
            'knowledge_category_id' => $category->id,
            'author_id' => $this->author->id,
        ], $overrides));
        $this->festival->knowledgeArticles()->attach($article->id);
        return $article;
    }
}
