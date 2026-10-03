<?php

namespace Tests\Feature\Events;

use App\Models\Event;
use App\Models\Festival;
use App\Models\Interprete;
use App\Models\KnowledgeArticle;
use App\Models\KnowledgeCategory;
use App\Models\Mes;
use App\Models\News;
use App\Models\PeniaProfile;
use App\Models\Provincia;
use App\Models\User;
use App\Services\Events\EventRelatedContentService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class EventDetailTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        // Sin response cache: algunos tests piden la misma URL dos veces con distinta configuración.
        config(['responsecache.enabled' => false, 'features.festival_journey' => false, 'features.festival_journey_allowlist' => []]);
    }

    /** CA1 */
    public function test_detail_shows_key_facts_before_the_body_and_a_ticket_button(): void
    {
        $provincia = $this->makeProvincia();
        $event = $this->makeEvent('Peña completa', [
            'start_at' => now()->addWeek()->setTime(21, 30),
            'city' => 'Cosquín',
            'address' => 'Plaza Próspero Molina',
            'province_id' => $provincia->id,
            'price_text' => '$ 15.000',
            'ticket_url' => 'https://entradas.example.test/pena',
            'body' => '<p>Cuerpo del evento completo.</p>',
        ]);

        $response = $this->get(route('cartelera.show', $event->slug));

        $response->assertOk()
            ->assertSeeInOrder(['21:30 h', 'Cosquín', 'Plaza Próspero Molina', $provincia->nombre, '$ 15.000', 'Comprar entradas', 'Cuerpo del evento completo.'], false)
            ->assertSee('href="https://entradas.example.test/pena" target="_blank" rel="noopener nofollow"', false)
            ->assertSee('Cómo llegar');
    }

    public function test_free_events_show_gratis_and_invalid_ticket_urls_are_not_rendered(): void
    {
        $event = $this->makeEvent('Peña gratuita', [
            'is_free' => true,
            'ticket_url' => 'javascript:alert(1)',
        ]);

        $this->get(route('cartelera.show', $event->slug))
            ->assertOk()
            ->assertSee('Gratis')
            ->assertDontSee('Comprar entradas')
            ->assertDontSee('javascript:alert(1)', false);
    }

    /** CA2 */
    public function test_detail_uses_the_full_width_of_the_layout_column(): void
    {
        $event = $this->makeEvent('Evento ancho completo');

        $this->get(route('cartelera.show', $event->slug))
            ->assertOk()
            ->assertDontSee('lg:w-2/3', false)
            ->assertDontSee('<div class="container mx-auto px-4 mt-4">', false);
    }

    /** CA3 */
    public function test_detail_has_a_compact_search_that_targets_the_listing(): void
    {
        $provincia = $this->makeProvincia();
        $event = $this->makeEvent('Evento con buscador');

        $this->get(route('cartelera.show', $event->slug))
            ->assertOk()
            ->assertSee('action="'.route('cartelera.index').'"', false)
            ->assertSee('name="province_id"', false)
            ->assertSee('name="mes"', false)
            ->assertSee('name="q"', false)
            ->assertDontSee('interpretes-list', false)
            ->assertDontSee('name="fecha"', false);

        $listing = $this->get(route('cartelera.index', ['province_id' => $provincia->id]));

        $listing->assertOk();
        $this->assertMatchesRegularExpression(
            '#<link rel="canonical" href="[^"]*/cartelera-de-eventos-folkloricos/'.preg_quote($provincia->slug, '#').'"#',
            $listing->getContent()
        );
        $this->assertSame($provincia->id, $listing->viewData('filters')['province_id']);
    }

    /** CA4 */
    public function test_active_artists_are_visible_without_the_festival_pilot(): void
    {
        $active = $this->makeArtist('Dúo visible');
        $inactive = $this->makeArtist('Solista oculto', ['estado' => 0]);
        $event = $this->makeEvent('Evento con artistas');
        $event->interpretes()->attach([$active->id => ['sort_order' => 1], $inactive->id => ['sort_order' => 2]]);

        $this->get(route('cartelera.show', $event->slug))
            ->assertOk()
            ->assertSee('Artistas en escena')
            ->assertSee('data-module="event_related_artists"', false)
            ->assertSee($active->interprete)
            ->assertDontSee($inactive->interprete);
    }

    /** CA5 */
    public function test_past_event_stays_indexable_and_offers_upcoming_dates_of_the_artist_and_province(): void
    {
        $provincia = $this->makeProvincia();
        $artist = $this->makeArtist('Artista con agenda');
        $past = $this->makeEvent('Recital ya realizado', ['start_at' => now()->subMonth(), 'province_id' => $provincia->id]);
        $artistNext = $this->makeEvent('Próxima fecha del artista', ['start_at' => now()->addDays(10)]);
        $provinceNext = $this->makeEvent('Otro evento de la provincia', ['start_at' => now()->addDays(12), 'province_id' => $provincia->id]);
        $artist->events()->attach([$past->id => ['sort_order' => 1], $artistNext->id => ['sort_order' => 1]]);

        $response = $this->get(route('cartelera.show', $past->slug));

        $response->assertOk()
            ->assertSee('Este evento ya se realizó')
            ->assertSee('Más de '.$artist->interprete)
            ->assertSee('Próximas fechas')
            ->assertSee($artistNext->title)
            ->assertSee('Próximos eventos en '.$provincia->nombre)
            ->assertSee($provinceNext->title)
            ->assertSee(route('artista.show', $artist->slug), false);

        $this->assertStringNotContainsString('noindex', $this->metaRobots($response));
    }

    public function test_artist_block_falls_back_to_previous_presentations_and_skips_hidden_events(): void
    {
        $artist = $this->makeArtist('Artista sin agenda');
        $current = $this->makeEvent('Evento actual del artista', ['start_at' => now()->subDays(3)]);
        $older = $this->makeEvent('Presentación anterior visible', ['start_at' => now()->subMonths(2)]);
        $draft = $this->makeEvent('Presentación en borrador', ['start_at' => now()->addDays(5), 'editorial_status' => 'draft']);
        $inactive = $this->makeEvent('Presentación inactiva', ['start_at' => now()->addDays(6), 'status' => 'inactive']);
        $artist->events()->attach([
            $current->id => ['sort_order' => 1],
            $older->id => ['sort_order' => 1],
            $draft->id => ['sort_order' => 1],
            $inactive->id => ['sort_order' => 1],
        ]);

        $this->get(route('cartelera.show', $current->slug))
            ->assertOk()
            ->assertSee('Más de '.$artist->interprete)
            ->assertSee('Presentaciones anteriores')
            ->assertSee($older->title)
            ->assertDontSee($draft->title)
            ->assertDontSee($inactive->title);
    }

    /** CA6 */
    public function test_breadcrumb_includes_the_province_landing(): void
    {
        $provincia = $this->makeProvincia();
        $event = $this->makeEvent('Evento con provincia', ['province_id' => $provincia->id]);

        $this->get(route('cartelera.show', $event->slug))
            ->assertOk()
            ->assertSeeInOrder([
                'aria-label="Breadcrumb"',
                route('cartelera.index'),
                url('/cartelera-de-eventos-folkloricos/'.$provincia->slug),
                $event->title,
            ], false);
    }

    /** CA7 */
    public function test_explore_by_province_only_lists_provinces_with_upcoming_public_events(): void
    {
        $withEvents = $this->makeProvincia();
        $withoutEvents = $this->makeProvincia();
        $onlyPast = $this->makeProvincia();
        $this->makeEvent('Evento futuro provincia A', ['province_id' => $withEvents->id, 'start_at' => now()->addDays(3)]);
        $this->makeEvent('Otro evento futuro provincia A', ['province_id' => $withEvents->id, 'start_at' => now()->addDays(4)]);
        $this->makeEvent('Evento pasado provincia C', ['province_id' => $onlyPast->id, 'start_at' => now()->subDays(4)]);
        $this->makeEvent('Evento borrador provincia B', ['province_id' => $withoutEvents->id, 'editorial_status' => 'draft']);
        $current = $this->makeEvent('Evento de referencia');

        foreach ([route('cartelera.index'), route('cartelera.show', $current->slug)] as $url) {
            $section = $this->provinceSection($this->get($url)->assertOk());

            $this->assertStringContainsString($withEvents->nombre, $section, $url);
            $this->assertMatchesRegularExpression('/'.preg_quote($withEvents->nombre, '/').'\s*<span[^>]*>2</', $section, $url);
            $this->assertStringNotContainsString($withoutEvents->nombre, $section, $url);
            $this->assertStringNotContainsString($onlyPast->nombre, $section, $url);
        }
    }

    public function test_minimal_event_renders_without_empty_blocks_or_placeholders(): void
    {
        $artist = $this->makeArtist('Artista único');
        $event = $this->makeEvent('Evento mínimo', ['city' => 'Tilcara', 'body' => null]);
        $event->interpretes()->attach($artist->id, ['sort_order' => 1]);

        $this->get(route('cartelera.show', $event->slug))
            ->assertOk()
            ->assertSee('Tilcara')
            ->assertSee($artist->interprete)
            ->assertDontSee('a confirmar')
            ->assertDontSee('<dt class="font-semibold text-slate-900">Provincia</dt>', false)
            ->assertDontSee('<dt class="font-semibold text-slate-900">Entrada</dt>', false)
            ->assertDontSee('Más de '.$artist->interprete)
            ->assertDontSee('Próximos eventos en')
            ->assertDontSee('Comprar entradas')
            ->assertDontSee('Cómo llegar')
            ->assertDontSee('Este evento ya se realizó');
    }

    public function test_event_without_artists_does_not_render_artist_blocks(): void
    {
        $event = $this->makeEvent('Evento sin artistas');

        $this->get(route('cartelera.show', $event->slug))
            ->assertOk()
            ->assertDontSee('Artistas en escena')
            ->assertDontSee('data-module="event_related_artist_events"', false);
    }

    /** CA10 */
    public function test_json_ld_is_a_complete_event_without_invented_offers(): void
    {
        $provincia = $this->makeProvincia();
        $event = $this->makeEvent('Evento con schema', [
            'city' => 'Jesús María',
            'address' => 'Anfiteatro José Hernández',
            'province_id' => $provincia->id,
            'end_at' => now()->addWeek()->addHours(5),
        ]);

        $schema = $this->eventSchema($this->get(route('cartelera.show', $event->slug))->assertOk());

        $this->assertSame('Event', $schema['@type']);
        $this->assertSame('https://schema.org/EventScheduled', $schema['eventStatus']);
        $this->assertSame('https://schema.org/OfflineEventAttendanceMode', $schema['eventAttendanceMode']);
        $this->assertArrayHasKey('endDate', $schema);
        $this->assertSame('Place', $schema['location']['@type']);
        $this->assertSame('PostalAddress', $schema['location']['address']['@type']);
        $this->assertSame('Jesús María', $schema['location']['address']['addressLocality']);
        $this->assertSame($provincia->nombre, $schema['location']['address']['addressRegion']);
        $this->assertSame('AR', $schema['location']['address']['addressCountry']);
        $this->assertArrayNotHasKey('offers', $schema);
        $this->assertArrayNotHasKey('organizer', $schema);
    }

    public function test_json_ld_offers_only_declare_loaded_data(): void
    {
        $event = $this->makeEvent('Evento con oferta', ['ticket_url' => 'https://entradas.example.test/x', 'price_text' => 'Desde $ 8.000']);

        $offers = $this->eventSchema($this->get(route('cartelera.show', $event->slug)))['offers'];

        $this->assertSame('Offer', $offers['@type']);
        $this->assertSame('https://entradas.example.test/x', $offers['url']);
        $this->assertSame('Desde $ 8.000', $offers['description']);
        $this->assertArrayNotHasKey('price', $offers);
        $this->assertArrayNotHasKey('availability', $offers);
    }

    public function test_slug_matching_a_province_keeps_resolving_to_the_province_landing(): void
    {
        $provincia = $this->makeProvincia();
        $this->makeEvent('Evento homónimo', ['slug' => $provincia->slug]);

        $response = $this->get(url('/cartelera-de-eventos-folkloricos/'.$provincia->slug));

        $response->assertOk()->assertViewIs('frontend.shows.index');
        $this->assertSame($provincia->id, $response->viewData('filters')['province_id']);
    }

    public function test_detail_and_index_respect_the_html_budget_and_a_single_h1(): void
    {
        $artist = $this->makeArtist('Artista presupuesto');
        $event = $this->makeEvent('Evento presupuesto');
        $event->interpretes()->attach($artist->id, ['sort_order' => 1]);

        foreach ([route('cartelera.show', $event->slug), route('cartelera.index')] as $url) {
            $html = $this->get($url)->assertOk()->getContent();

            $this->assertLessThanOrEqual(350 * 1024, strlen($html), $url.' excede el presupuesto HTML de 350 KB.');
            $this->assertSame(1, preg_match_all('/<h1\b/i', $html), $url.' debe contener un único h1.');
        }
    }

    public function test_hero_image_is_eager_and_cards_are_lazy(): void
    {
        $artist = $this->makeArtist('Artista imágenes');
        $event = $this->makeEvent('Evento imágenes');
        $next = $this->makeEvent('Evento imágenes siguiente', ['start_at' => now()->addDays(20)]);
        $artist->events()->attach([$event->id => ['sort_order' => 1], $next->id => ['sort_order' => 1]]);

        $html = $this->get(route('cartelera.show', $event->slug))->assertOk()->getContent();

        $this->assertMatchesRegularExpression('/<img[^>]+loading="eager"[^>]+fetchpriority="high"|<img[^>]+fetchpriority="high"[^>]+loading="eager"/', $html);
        $this->assertStringContainsString('loading="lazy"', $html);
        $this->assertStringNotContainsString('<iframe', $html);
    }

    /** CA8 */
    public function test_detail_shows_festival_context_and_derived_news_blocks(): void
    {
        $artist = $this->makeArtist('Artista con noticias');
        $event = $this->makeEvent('Evento vinculado');
        $event->interpretes()->attach($artist->id, ['sort_order' => 1]);
        $festival = $this->makeFestival('Festival vinculado');
        $event->festivales()->attach($festival->id);
        $article = $this->makeArticle('Historia del chamamé', ['editorial_status' => 'published', 'published_at' => now()->subDay()]);
        $event->knowledgeArticles()->attach($article->id);

        $artistNews = $this->makeNews('Noticia del artista', ['interprete_id' => $artist->id, 'published_at' => now()->subDays(2)]);
        $festivalNews = $this->makeNews('Noticia del festival', ['published_at' => now()->subDay()]);
        $festivalNews->festivales()->attach($festival->id);

        $response = $this->get(route('cartelera.show', $event->slug))->assertOk();

        $response->assertSee('Forma parte de')
            ->assertSee('data-module="event_related_festivals"', false)
            ->assertSee($festival->title)
            ->assertSee('Historia y contexto')
            ->assertSee('data-module="event_related_knowledge"', false)
            ->assertSee($article->title)
            ->assertSee('Noticias relacionadas')
            ->assertSee('data-module="event_related_news"', false)
            ->assertSeeInOrder([$festivalNews->title, $artistNews->title])
            ->assertSee(route('artista.noticia', [$artist->slug, $artistNews->slug]), false);

        $schema = $this->eventSchema($response);
        $this->assertSame($festival->title, $schema['superEvent']['name']);
    }

    /** CA9 */
    public function test_unpublished_related_content_is_not_listed(): void
    {
        $artist = $this->makeArtist('Artista con borradores');
        $event = $this->makeEvent('Evento con borradores');
        $event->interpretes()->attach($artist->id, ['sort_order' => 1]);
        $event->festivales()->attach($this->makeFestival('Festival borrador', ['status' => 'draft'])->id);
        $event->knowledgeArticles()->attach($this->makeArticle('Artículo borrador')->id);
        $this->makeNews('Noticia borrador', ['interprete_id' => $artist->id, 'editorial_status' => 'draft']);
        $this->makeNews('Noticia programada', ['interprete_id' => $artist->id, 'published_at' => now()->addDay()]);

        $this->get(route('cartelera.show', $event->slug))
            ->assertOk()
            ->assertDontSee('Forma parte de')
            ->assertDontSee('Historia y contexto')
            ->assertDontSee('Noticias relacionadas')
            ->assertDontSee('Noticia borrador')
            ->assertDontSee('Noticia programada');
    }

    public function test_penias_respect_the_directory_flag_and_public_verification(): void
    {
        $event = $this->makeEvent('Evento en peña');
        $visible = PeniaProfile::factory()->create([
            'title' => 'Peña verificada '.uniqid(),
            'editorial_status' => 'published',
            'published_at' => now()->subDay(),
            'verification_status' => 'verified',
            'verified_by_user_id' => User::factory()->create()->id,
            'verification_method' => 'phone',
            'last_verified_at' => now()->subDays(5),
        ]);
        $unverified = PeniaProfile::factory()->create(['title' => 'Peña sin verificar '.uniqid(), 'editorial_status' => 'published', 'published_at' => now()->subDay()]);
        $event->peniaProfiles()->attach([$visible->id, $unverified->id]);

        config(['features.penia_directory' => false]);
        $this->get(route('cartelera.show', $event->slug))->assertOk()->assertDontSee($visible->title);

        config(['features.penia_directory' => true]);
        $this->get(route('cartelera.show', $event->slug))
            ->assertOk()
            ->assertSee('data-module="event_related_penias"', false)
            ->assertSee($visible->title)
            ->assertDontSee($unverified->title);
    }

    public function test_derived_news_are_limited_to_three_without_duplicates(): void
    {
        $artist = $this->makeArtist('Artista prolífico');
        $event = $this->makeEvent('Evento con muchas noticias');
        $event->interpretes()->attach($artist->id, ['sort_order' => 1]);

        foreach (range(1, 4) as $i) {
            $news = $this->makeNews("Nota prolífica {$i}", ['interprete_id' => $artist->id, 'published_at' => now()->subDays($i)]);
            $news->interpretes()->attach($artist->id);
        }

        $related = app(EventRelatedContentService::class)->forEvent($event->load('interpretes'));

        $this->assertCount(3, $related['news']);
        $this->assertSame($related['news']->modelKeys(), array_unique($related['news']->modelKeys()));
        $this->assertStringStartsWith('Nota prolífica 1', $related['news']->first()->title);
    }

    // ------------------------------------------------------------------

    private function makeFestival(string $title, array $attributes = []): Festival
    {
        return Festival::create(array_merge([
            'title' => $title.' '.uniqid(),
            'slug' => Str::slug($title).'-'.uniqid(),
            'body' => '<p>Festival de prueba.</p>',
            'province_id' => $this->makeProvincia()->id,
            'mes_id' => Mes::firstOrCreate(['nombre' => 'Febrero'])->id,
            'user_id' => User::factory()->create()->id,
            'status' => 'published',
            'published_at' => now()->subDay(),
            'visitas' => 0,
        ], $attributes));
    }

    private function makeArticle(string $title, array $attributes = []): KnowledgeArticle
    {
        $category = KnowledgeCategory::factory()->create(['slug' => 'categoria-test-'.uniqid()]);

        return KnowledgeArticle::factory()->create(array_merge([
            'knowledge_category_id' => $category->id,
            'title' => $title.' '.uniqid(),
            'slug' => Str::slug($title).'-'.uniqid(),
        ], $attributes));
    }

    private function makeNews(string $title, array $attributes = []): News
    {
        $title .= ' '.uniqid();

        return News::create(array_merge([
            'title' => $title,
            'slug' => Str::slug($title),
            'body' => '<p>Noticia de prueba.</p>',
            'editorial_status' => 'published',
            'published_at' => now()->subDay(),
        ], $attributes));
    }

    protected function makeEvent(string $title, array $attributes = []): Event
    {
        return Event::create(array_merge([
            'title' => $title,
            'slug' => Str::slug($title).'-'.uniqid(),
            'body' => '<p>Contenido de prueba.</p>',
            'start_at' => now()->addWeek(),
            'editorial_status' => 'published',
            'status' => 'active',
            'modality' => 'presencial',
        ], $attributes));
    }

    protected function makeArtist(string $name, array $attributes = []): Interprete
    {
        return Interprete::create(array_merge([
            'interprete' => $name.' '.uniqid(),
            'slug' => Str::slug($name).'-'.uniqid(),
            'biografia' => 'Biografía de prueba.',
            'estado' => 1,
        ], $attributes));
    }

    protected function makeProvincia(): Provincia
    {
        return Provincia::create(['nombre' => 'Provincia Test '.Str::upper(Str::random(8))]);
    }

    private function provinceSection(TestResponse $response): string
    {
        $html = $response->getContent();
        $start = strpos($html, 'Explorar por provincia');
        $this->assertNotFalse($start, 'Falta el bloque Explorar por provincia.');

        return substr($html, $start, strpos($html, '</section>', $start) - $start);
    }

    private function metaRobots(TestResponse $response): string
    {
        preg_match('/<meta name="robots" content="([^"]*)"/i', $response->getContent(), $matches);

        return $matches[1] ?? '';
    }

    protected function eventSchema(TestResponse $response): array
    {
        preg_match_all('#<script type="application/ld\+json">\s*(.*?)\s*</script>#s', $response->getContent(), $matches);

        foreach ($matches[1] as $json) {
            $data = json_decode($json, true);
            if (($data['@type'] ?? null) === 'Event') {
                return $data;
            }
        }

        $this->fail('No se encontró JSON-LD de tipo Event.');
    }
}
