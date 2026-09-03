<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Interprete;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class PublicEventVisibilityTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function global_search_only_returns_active_events_whose_publication_is_visible(): void
    {
        $visible = $this->makeEvent('Coincidencia publica visible', [
            'published_at' => now()->subHour(),
        ]);
        $draft = $this->makeEvent('Coincidencia publica borrador', [
            'editorial_status' => 'draft',
        ]);
        $future = $this->makeEvent('Coincidencia publica futura', [
            'published_at' => now()->addDay(),
        ]);
        $inactive = $this->makeEvent('Coincidencia publica inactiva', [
            'status' => 'inactive',
        ]);

        $response = $this->get(route('buscar', ['q' => 'Coincidencia publica']));

        $response->assertOk();
        $response->assertViewHas('resultados', function (array $results) use ($visible, $draft, $future, $inactive) {
            $ids = $results['shows']->modelKeys();

            return in_array($visible->id, $ids, true)
                && ! in_array($draft->id, $ids, true)
                && ! in_array($future->id, $ids, true)
                && ! in_array($inactive->id, $ids, true);
        });
    }

    /** @test */
    public function public_event_detail_returns_not_found_for_non_public_events(): void
    {
        $visible = $this->makeEvent('Detalle publico visible', [
            'published_at' => now()->subHour(),
        ]);
        $draft = $this->makeEvent('Detalle publico borrador', [
            'editorial_status' => 'draft',
        ]);
        $future = $this->makeEvent('Detalle publico futuro', [
            'published_at' => now()->addDay(),
        ]);
        $inactive = $this->makeEvent('Detalle publico inactivo', [
            'status' => 'inactive',
        ]);

        $this->get(route('cartelera.show', $visible->slug))->assertOk();
        $this->get(route('cartelera.show', $draft->slug))->assertNotFound();
        $this->get(route('cartelera.show', $future->slug))->assertNotFound();
        $this->get(route('cartelera.show', $inactive->slug))->assertNotFound();
    }

    /** @test */
    public function artist_events_only_list_active_events_whose_publication_is_visible(): void
    {
        $artist = Interprete::create([
            'interprete' => 'Artista visibilidad '.uniqid(),
            'slug' => 'artista-visibilidad-'.uniqid(),
            'biografia' => 'Biografia de prueba.',
            'estado' => 1,
        ]);

        $visible = $this->makeEvent('Agenda artista visible', [
            'published_at' => now()->subHour(),
        ]);
        $draft = $this->makeEvent('Agenda artista borrador', [
            'editorial_status' => 'draft',
        ]);
        $future = $this->makeEvent('Agenda artista futura', [
            'published_at' => now()->addDay(),
        ]);
        $inactive = $this->makeEvent('Agenda artista inactiva', [
            'status' => 'inactive',
        ]);

        $artist->events()->sync([$visible->id, $draft->id, $future->id, $inactive->id]);

        $response = $this->get(route('artista.shows', $artist->slug));

        $response->assertOk();
        $response->assertSee($visible->title);
        $response->assertDontSee($draft->title);
        $response->assertDontSee($future->title);
        $response->assertDontSee($inactive->title);
    }

    private function makeEvent(string $title, array $attributes = []): Event
    {
        return Event::create(array_merge([
            'title' => $title,
            'slug' => str($title)->slug()->append('-'.uniqid())->toString(),
            'body' => 'Contenido de prueba.',
            'start_at' => now()->addWeek(),
            'editorial_status' => 'published',
            'status' => 'active',
        ], $attributes));
    }
}
