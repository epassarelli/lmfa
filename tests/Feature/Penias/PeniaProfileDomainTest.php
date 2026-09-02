<?php

namespace Tests\Feature\Penias;

use App\Models\Event;
use App\Models\PeniaProfile;
use App\Models\Provincia;
use App\Models\User;
use App\Services\PeniaProfileService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class PeniaProfileDomainTest extends TestCase
{
    use DatabaseTransactions;

    public function test_a_currently_verified_profile_can_be_published_and_linked_to_an_event(): void
    {
        $user = User::factory()->create();
        $province = Provincia::create(['nombre' => 'Provincia Peña '.uniqid()]);
        $event = Event::create([
            'title' => 'Evento de Peña '.uniqid(),
            'body' => '<p>Agenda futura.</p>',
            'start_at' => now()->addWeek(),
            'province_id' => $province->id,
            'city' => 'Ciudad de prueba',
            'status' => 'active',
            'editorial_status' => 'published',
            'published_at' => now()->subDay(),
            'created_by' => $user->id,
        ]);

        $profile = app(PeniaProfileService::class)->save(new PeniaProfile(), [
            'title' => 'Peña publicada '.uniqid(),
            'body' => '<p>Espacio cultural verificado.</p>',
            'province_id' => $province->id,
            'city' => 'Ciudad de prueba',
            'venue_type' => 'penia',
            'source_urls' => ['https://example.test/penia'],
            'verification_status' => 'verified',
            'last_verified_at' => now(),
            'verified_by_user_id' => $user->id,
            'verification_method' => 'official_source',
            'editorial_status' => 'published',
            'created_by' => $user->id,
            'event_ids' => [$event->id],
        ]);

        $this->assertTrue($profile->fresh()->isPublished());
        $this->assertSame([$event->id], $profile->events()->pluck('events.id')->all());
        $this->assertSame([$profile->id], $event->peniaProfiles()->pluck('penia_profiles.id')->all());
        $this->assertTrue(PeniaProfile::publiclyVisible()->whereKey($profile)->exists());
    }

    public function test_an_expired_verification_cannot_be_published_or_exposed_publicly(): void
    {
        $user = User::factory()->create();
        $province = Provincia::create(['nombre' => 'Provincia vencida '.uniqid()]);

        $this->expectException(ValidationException::class);

        app(PeniaProfileService::class)->save(new PeniaProfile(), [
            'title' => 'Peña vencida '.uniqid(),
            'body' => '<p>Datos vencidos.</p>',
            'province_id' => $province->id,
            'venue_type' => 'penia',
            'source_urls' => ['https://example.test/penia-vencida'],
            'verification_status' => 'verified',
            'last_verified_at' => now()->subDays(91),
            'verified_by_user_id' => $user->id,
            'verification_method' => 'official_source',
            'editorial_status' => 'published',
            'created_by' => $user->id,
        ]);
    }
}
