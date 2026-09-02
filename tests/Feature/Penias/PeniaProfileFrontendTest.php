<?php

namespace Tests\Feature\Penias;

use App\Models\Event;
use App\Models\PeniaProfile;
use App\Models\Provincia;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class PeniaProfileFrontendTest extends TestCase
{
    use DatabaseTransactions;

    public function test_the_directory_and_profile_only_expose_currently_verified_content_and_future_events(): void
    {
        $user = User::factory()->create();
        $province = Provincia::create(['nombre' => 'Provincia pública '.uniqid()]);
        $profile = PeniaProfile::factory()->create([
            'title' => 'Peña pública', 'slug' => 'penia-publica', 'province_id' => $province->id,
            'verification_status' => 'verified', 'last_verified_at' => now(), 'verified_by_user_id' => $user->id,
            'verification_method' => 'official_source', 'editorial_status' => 'published', 'published_at' => now()->subDay(),
        ]);
        $expired = PeniaProfile::factory()->create([
            'title' => 'Peña vencida', 'slug' => 'penia-vencida', 'province_id' => $province->id,
            'verification_status' => 'verified', 'last_verified_at' => now()->subDays(91), 'verified_by_user_id' => $user->id,
            'verification_method' => 'official_source', 'editorial_status' => 'published', 'published_at' => now()->subDay(),
        ]);
        $future = Event::create(['title' => 'Agenda futura', 'body' => '<p>Futura</p>', 'start_at' => now()->addDay(), 'province_id' => $province->id, 'city' => 'Ciudad', 'status' => 'active', 'editorial_status' => 'published', 'published_at' => now()->subDay(), 'created_by' => $user->id]);
        $past = Event::create(['title' => 'Agenda pasada', 'body' => '<p>Pasada</p>', 'start_at' => now()->subDay(), 'province_id' => $province->id, 'city' => 'Ciudad', 'status' => 'active', 'editorial_status' => 'published', 'published_at' => now()->subDay(), 'created_by' => $user->id]);
        $profile->events()->attach([$future->id, $past->id]);

        $this->get('/penias')->assertOk()->assertSee('Peña pública')->assertDontSee('Peña vencida');
        $this->get('/penias/penia-publica')->assertOk()->assertSee('Agenda futura')->assertDontSee('Agenda pasada')->assertSee('MusicVenue');
        $this->get('/penias/penia-vencida')->assertNotFound();
        $this->get('/sitemap-penias.xml')->assertOk()->assertSee('/penias/penia-publica', false)->assertDontSee('/penias/penia-vencida', false);
    }
}
