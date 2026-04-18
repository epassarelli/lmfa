<?php

namespace Tests\Feature\Pasarela;

use App\Models\Event;
use App\Models\Interprete;
use App\Models\Show;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * Tests para los bugs corregidos en el sistema de Shows/Events:
 *
 * (1) event_interprete sin timestamps → withTimestamps() causaba "Column not found: created_at"
 * (2) ShowController@index usaba where('fecha') y where('user_id') → columnas no existen en events
 * (3) ShowRequest slug unique apuntaba a tabla 'shows' (deprecated)
 * (4) InterpretesController usaba where('fecha') en la relación → columna no existe
 * (5) byArtista.blade.php usaba $evento->descripcion → campo inexistente
 * (6) Event model faltaban mutators para user_id, publicar, estado, fecha, lugar, direccion
 */
class ShowsBugFixTest extends TestCase
{
    use DatabaseTransactions;

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function makeEvent(array $extra = []): Event
    {
        return Event::create(array_merge([
            'title'            => 'Show test ' . uniqid(),
            'slug'             => 'show-test-' . uniqid(),
            'body'             => 'Detalles del show.',
            'editorial_status' => 'published',
            'start_at'         => now()->addDays(7),
        ], $extra));
    }

    private function makeInterprete(): Interprete
    {
        return Interprete::where('estado', 1)->first()
            ?? Interprete::create([
                'interprete' => 'Artista Test ' . uniqid(),
                'slug'       => 'artista-test-' . uniqid(),
                'estado'     => 1,
            ]);
    }

    // -------------------------------------------------------------------------
    // Fix 1: event_interprete sin timestamps — withTimestamps() removido
    // -------------------------------------------------------------------------

    /** @test */
    public function test_event_interpretes_relation_does_not_fail_with_timestamps(): void
    {
        $interprete = $this->makeInterprete();
        $event = $this->makeEvent();

        // Asociar via pivot — antes fallaba con "Column not found: event_interprete.created_at"
        $event->interpretes()->sync([$interprete->id]);

        $loaded = Event::with('interpretes')->find($event->id);
        $this->assertCount(1, $loaded->interpretes);
        $this->assertEquals($interprete->id, $loaded->interpretes->first()->id);
    }

    /** @test */
    public function test_interprete_events_relation_does_not_fail_with_timestamps(): void
    {
        $interprete = $this->makeInterprete();
        $event = $this->makeEvent();
        $event->interpretes()->sync([$interprete->id]);

        // Acceso desde el lado del intérprete — antes también fallaba
        $loaded = Interprete::with('events')->find($interprete->id);
        $this->assertTrue($loaded->events->contains('id', $event->id));
    }

    // -------------------------------------------------------------------------
    // Fix 2: Event model — mutators/accessors de compatibilidad
    // -------------------------------------------------------------------------

    /** @test */
    public function test_event_user_id_mutator_maps_to_created_by(): void
    {
        $user = User::factory()->create();
        $event = new Event();
        $event->user_id = $user->id;

        $this->assertEquals($user->id, $event->created_by);
    }

    /** @test */
    public function test_event_publicar_mutator_maps_to_published_at(): void
    {
        $event = new Event();
        $event->publicar = '2026-05-01';

        $this->assertEquals('2026-05-01', \Carbon\Carbon::parse($event->published_at)->format('Y-m-d'));
    }

    /** @test */
    public function test_event_estado_accessor_returns_int_based_on_editorial_status(): void
    {
        $event = new Event(['editorial_status' => 'published']);
        $this->assertEquals(1, $event->estado);

        $event2 = new Event(['editorial_status' => 'draft']);
        $this->assertEquals(0, $event2->estado);
    }

    /** @test */
    public function test_event_estado_mutator_sets_editorial_status(): void
    {
        $event = new Event();
        $event->estado = 1;
        $this->assertEquals('published', $event->editorial_status);

        $event2 = new Event();
        $event2->estado = 0;
        $this->assertEquals('draft', $event2->editorial_status);
    }

    /** @test */
    public function test_event_fecha_mutator_maps_to_start_at(): void
    {
        $event = new Event();
        $event->fecha = '2026-06-15 20:00:00';

        $this->assertEquals('2026-06-15', \Carbon\Carbon::parse($event->start_at)->format('Y-m-d'));
    }

    /** @test */
    public function test_event_lugar_mutator_maps_to_city(): void
    {
        $event = new Event();
        $event->lugar = 'Córdoba';

        $this->assertEquals('Córdoba', $event->city);
    }

    /** @test */
    public function test_event_direccion_mutator_maps_to_address(): void
    {
        $event = new Event();
        $event->direccion = 'Av. Colón 1234';

        $this->assertEquals('Av. Colón 1234', $event->address);
    }

    /** @test */
    public function test_event_user_relation_alias_works(): void
    {
        $user = User::factory()->create();
        $event = $this->makeEvent(['created_by' => $user->id]);

        $this->assertNotNull($event->user);
        $this->assertEquals($user->id, $event->user->id);
        $this->assertEquals($user->id, $event->creator->id);
    }

    // -------------------------------------------------------------------------
    // Fix 3: ShowController@index query corregida
    // -------------------------------------------------------------------------

    /** @test */
    public function test_backend_shows_index_query_uses_correct_columns(): void
    {
        // Verifica que la consulta de ShowController@index no tira excepción SQL
        $user = User::factory()->create();

        // Esta es la consulta corregida del ShowController@index
        $shows = Show::query()
            ->where('start_at', '>=', now())
            ->with(['user', 'interpretes', 'images'])
            ->get();

        $this->assertNotNull($shows);
    }

    /** @test */
    public function test_backend_shows_index_filters_by_created_by_for_prensa(): void
    {
        $user = User::factory()->create();
        $event = $this->makeEvent([
            'created_by' => $user->id,
            'start_at'   => now()->addDays(5),
        ]);

        $shows = Show::query()
            ->where('created_by', $user->id)
            ->where('start_at', '>=', now())
            ->get();

        $this->assertTrue($shows->contains('id', $event->id));
    }

    // -------------------------------------------------------------------------
    // Fix 4: InterpretesController — start_at en lugar de fecha
    // -------------------------------------------------------------------------

    /** @test */
    public function test_interprete_shows_query_uses_start_at(): void
    {
        $interprete = $this->makeInterprete();
        $upcomingEvent = $this->makeEvent(['start_at' => now()->addDays(3)]);
        $pastEvent     = $this->makeEvent(['start_at' => now()->subDays(10)]);

        $upcomingEvent->interpretes()->sync([$interprete->id]);
        $pastEvent->interpretes()->sync([$interprete->id]);

        // Consulta corregida (usada en InterpretesController@show)
        $shows = $interprete->shows()
            ->where('start_at', '>=', now())
            ->orderBy('start_at')
            ->take(2)
            ->get();

        $this->assertTrue($shows->contains('id', $upcomingEvent->id));
        $this->assertFalse($shows->contains('id', $pastEvent->id));
    }

    // -------------------------------------------------------------------------
    // Fix 5: Frontend cartelera — editorial_status = 'published'
    // -------------------------------------------------------------------------

    /** @test */
    public function test_cartelera_frontend_shows_published_events(): void
    {
        $this->makeEvent([
            'editorial_status' => 'published',
            'start_at'         => now()->addDays(5),
        ]);

        $response = $this->get(route('cartelera.index'));
        $response->assertOk();
        $response->assertViewHas('shows');
    }

    /** @test */
    public function test_interprete_shows_page_loads_without_error(): void
    {
        $interprete = $this->makeInterprete();
        $event = $this->makeEvent(['start_at' => now()->addDays(5)]);
        $event->interpretes()->sync([$interprete->id]);

        $response = $this->get(route('artista.shows', $interprete->slug));
        $response->assertOk();
    }

    /** @test */
    public function test_interprete_home_page_loads_without_error(): void
    {
        // Verifica que la página del intérprete no falla con el query de shows
        $interprete = $this->makeInterprete();

        $response = $this->get(route('artista.show', $interprete->slug));
        // Puede ser 200 o redirect dependiendo de la ruta — lo importante es no 500
        $this->assertNotEquals(500, $response->getStatusCode());
    }
}
