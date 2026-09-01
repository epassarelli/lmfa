<?php

namespace Tests\Feature\Directories;

use App\Models\Event;
use App\Models\Venue;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PublicDirectoryDetailTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function radio_directory_links_to_and_renders_its_own_published_detail(): void
    {
        DB::table('radios')->insert([
            'titulo' => 'Radio Horizonte Folklórico',
            'slug' => 'radio-horizonte-folklorico',
            'detalle' => '<p>Folklore argentino durante todo el día.</p>',
            'foto' => 'radio-horizonte.jpg',
            'escucharOnline' => 'https://example.com/radio',
            'visitas' => 0,
            'publicar' => now()->subDay(),
            'estado' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $detailUrl = route('radios.show', 'radio-horizonte-folklorico');

        $this->get(route('radios.index'))
            ->assertOk()
            ->assertSee('href="'.$detailUrl.'"', false);

        $this->get($detailUrl)
            ->assertOk()
            ->assertViewIs('frontend.radios.show')
            ->assertSee('Radio Horizonte Folklórico')
            ->assertSee('Folklore argentino durante todo el día.')
            ->assertSee('https://example.com/radio', false);
    }

    /** @test */
    public function penia_directory_links_to_and_renders_its_own_published_detail(): void
    {
        DB::table('penias')->insert([
            'titulo' => 'Peña La Salamanca',
            'slug' => 'penia-la-salamanca',
            'detalle' => '<p>Encuentro de música y danza tradicional.</p>',
            'foto' => 'penia-la-salamanca.jpg',
            'visitas' => 0,
            'publicar' => now()->subDay(),
            'estado' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $detailUrl = route('penias.show', 'penia-la-salamanca');

        $this->get(route('penias.index'))
            ->assertOk()
            ->assertSee('href="'.$detailUrl.'"', false);

        $this->get($detailUrl)
            ->assertOk()
            ->assertViewIs('frontend.penias.show')
            ->assertSee('Peña La Salamanca')
            ->assertSee('Encuentro de música y danza tradicional.');
    }

    /** @test */
    public function inactive_directory_entries_are_not_publicly_accessible(): void
    {
        DB::table('radios')->insert([
            'titulo' => 'Radio inactiva',
            'slug' => 'radio-inactiva',
            'detalle' => 'Contenido reservado.',
            'foto' => 'radio-inactiva.jpg',
            'escucharOnline' => 'https://example.com/inactiva',
            'visitas' => 0,
            'estado' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('penias')->insert([
            'titulo' => 'Peña inactiva',
            'slug' => 'penia-inactiva',
            'detalle' => 'Contenido reservado.',
            'foto' => 'penia-inactiva.jpg',
            'visitas' => 0,
            'estado' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->get(route('radios.show', 'radio-inactiva'))->assertNotFound();
        $this->get(route('penias.show', 'penia-inactiva'))->assertNotFound();
    }

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
