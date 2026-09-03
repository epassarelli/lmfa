<?php

namespace Tests\Feature\Penias;

use App\Models\Event;
use App\Models\Locality;
use App\Models\PeniaProfile;
use App\Models\Provincia;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class PeniaProfileFrontendTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        config(['features.penia_directory' => true]);
    }

    public function test_the_directory_and_profile_only_expose_currently_verified_content_and_future_events(): void
    {
        $user = User::factory()->create();
        $province = Provincia::create(['nombre' => 'Provincia pública '.uniqid()]);
        $locality = Locality::create(['province_id' => $province->id, 'name' => 'Localidad pública '.uniqid()]);
        $profile = PeniaProfile::factory()->create([
            'title' => 'Peña pública', 'slug' => 'penia-publica', 'province_id' => $province->id,
            'locality_id' => $locality->id, 'city' => 'Ciudad pública', 'address' => 'Calle pública 123', 'phone' => '+54 11 5555-1234',
            'latitude' => -34.6037, 'longitude' => -58.3816, 'capacity' => 120, 'accessibility_notes' => 'Acceso de prueba',
            'verification_status' => 'verified', 'last_verified_at' => now(), 'verified_by_user_id' => $user->id,
            'verification_method' => 'official_source', 'editorial_status' => 'published', 'published_at' => now()->subDay(),
        ]);
        $expired = PeniaProfile::factory()->create([
            'title' => 'Peña vencida', 'slug' => 'penia-vencida', 'province_id' => $province->id,
            'verification_status' => 'verified', 'last_verified_at' => now()->subDays(91), 'verified_by_user_id' => $user->id,
            'verification_method' => 'official_source', 'editorial_status' => 'published', 'published_at' => now()->subDay(),
        ]);
        $sameProvince = PeniaProfile::factory()->create([
            'title' => 'Peña vecina', 'slug' => 'penia-vecina', 'province_id' => $province->id,
            'verification_status' => 'verified', 'last_verified_at' => now(), 'verified_by_user_id' => $user->id,
            'verification_method' => 'official_source', 'editorial_status' => 'published', 'published_at' => now()->subDay(),
        ]);
        $future = Event::create(['title' => 'Agenda futura', 'body' => '<p>Futura</p>', 'start_at' => now()->addDay(), 'province_id' => $province->id, 'city' => 'Ciudad', 'status' => 'active', 'editorial_status' => 'published', 'published_at' => now()->subDay(), 'created_by' => $user->id]);
        $past = Event::create(['title' => 'Agenda pasada', 'body' => '<p>Pasada</p>', 'start_at' => now()->subDay(), 'province_id' => $province->id, 'city' => 'Ciudad', 'status' => 'active', 'editorial_status' => 'published', 'published_at' => now()->subDay(), 'created_by' => $user->id]);
        $unpublished = Event::create(['title' => 'Agenda no pública', 'body' => '<p>Oculta</p>', 'start_at' => now()->addDays(2), 'province_id' => $province->id, 'city' => 'Ciudad', 'status' => 'active', 'editorial_status' => 'draft', 'created_by' => $user->id]);
        $profile->events()->attach([$future->id, $past->id, $unpublished->id]);

        $this->get('/penias')->assertOk()->assertSee('Peña pública')->assertDontSee('Peña vencida');
        $this->get('/penias/penia-publica')->assertOk()->assertSee('Agenda futura')->assertDontSee('Agenda pasada')->assertDontSee('Agenda no pública')->assertSee('MusicVenue')->assertSee('Encontrá otra Peña')->assertSee('Calle pública 123')->assertSee('Localidad pública')->assertSee('+54 11 5555-1234')->assertSee('openstreetmap.org/export/embed.html', false)->assertSee('Peña vecina')->assertDontSee('Peña vencida');
        $this->get('/penias/penia-vencida')->assertNotFound();
        $this->get('/sitemap-penias.xml')->assertOk()->assertSee('/penias/penia-publica', false)->assertDontSee('/penias/penia-vencida', false);
    }

    public function test_the_directory_preserves_search_pagination_and_noindexes_arbitrary_filters(): void
    {
        $user = User::factory()->create();
        $province = Provincia::create(['nombre' => 'Provincia paginación '.uniqid()]);

        foreach (range(1, 13) as $position) {
            PeniaProfile::factory()->create([
                'title' => sprintf('Peña paginada %02d', $position),
                'slug' => sprintf('penia-paginada-%02d', $position),
                'province_id' => $province->id,
                'verification_status' => 'verified',
                'last_verified_at' => now(),
                'verified_by_user_id' => $user->id,
                'verification_method' => 'official_source',
                'editorial_status' => 'published',
                'published_at' => now()->subDay(),
            ]);
        }

        $this->get('/penias?q=paginada')
            ->assertOk()
            ->assertSee('Peña paginada 01')
            ->assertSee('noindex,follow', false)
            ->assertSee(route('penia-profiles.index'), false)
            ->assertSee('q=paginada', false);

        $this->get('/penias?q=paginada&page=2')
            ->assertOk()
            ->assertSee('Peña paginada 13')
            ->assertDontSee('Peña paginada 01');
    }

    public function test_the_retired_legacy_penia_urls_are_not_exposed(): void
    {
        $this->get('/penias-folkloricas-de-argentina')->assertNotFound();
        $this->get('/penias-folkloricas-de-argentina/slug-inexistente')->assertNotFound();
    }
}
