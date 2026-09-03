<?php

namespace Tests\Feature\Radios;

use App\Models\Provincia;
use App\Models\RadioProgram;
use App\Models\RadioSignal;
use App\Models\User;
use App\Services\RadioDirectoryService;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class RadioDirectoryDomainTest extends TestCase
{
    use DatabaseTransactions;

    public function test_it_publishes_an_air_signal_with_frequency_and_location(): void
    {
        $editor = User::factory()->create();
        $province = Provincia::first() ?: Provincia::create(['nombre' => 'Cordoba']);
        $signal = RadioSignal::factory()->make([
            'transmission_modes' => ['air'],
            'province_id' => $province->id,
            'city' => 'Cosquin',
            'verified_by_user_id' => $editor->id,
            'verification_method' => 'manual',
            'verification_status' => 'verified',
            'last_verified_at' => now(),
        ]);

        $published = app(RadioDirectoryService::class)->saveSignal($signal, [
            ...$signal->toArray(),
            'transmission_modes' => ['air'],
            'editorial_status' => 'published',
            'channels' => [[
                'label' => 'FM 99.5',
                'channel_type' => 'frequency',
                'frequency_band' => 'FM',
                'frequency' => '99.5',
            ]],
        ]);

        $this->assertSame('published', $published->fresh()->editorial_status);
        $this->assertTrue($published->listeningChannels()->where('frequency', '99.5')->exists());
    }

    public function test_it_rejects_a_digital_signal_without_an_active_listening_url(): void
    {
        $editor = User::factory()->create();
        $signal = RadioSignal::factory()->make([
            'verified_by_user_id' => $editor->id,
            'verification_method' => 'manual',
            'verification_status' => 'verified',
            'last_verified_at' => now(),
        ]);

        try {
            app(RadioDirectoryService::class)->saveSignal($signal, [
                ...$signal->toArray(),
                'editorial_status' => 'published',
                'channels' => [['label' => 'Sitio institucional', 'channel_type' => 'website']],
            ]);
            $this->fail('La señal digital no debió publicarse sin una URL de escucha.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('editorial_status', $exception->errors());
        }
    }

    public function test_it_publishes_an_independent_folklore_program_and_calculates_its_next_slot(): void
    {
        $editor = User::factory()->create();
        $program = RadioProgram::factory()->make([
            'platform' => 'youtube',
            'listening_url' => 'https://youtube.com/@ejemplo/live',
            'verified_by_user_id' => $editor->id,
            'verification_method' => 'manual',
            'verification_status' => 'verified',
            'last_verified_at' => now(),
        ]);

        $published = app(RadioDirectoryService::class)->saveProgram($program, [
            ...$program->toArray(),
            'editorial_status' => 'published',
            'slots' => [['weekday' => 1, 'starts_at' => '20:00', 'ends_at' => '22:00']],
        ]);

        $slot = $published->slots()->firstOrFail();
        $next = $slot->nextStartsAt(CarbonImmutable::parse('2026-09-01 21:00:00', 'America/Argentina/Buenos_Aires'));

        $this->assertSame('published', $published->fresh()->editorial_status);
        $this->assertSame('2026-09-07 20:00:00', $next->format('Y-m-d H:i:s'));
    }

    public function test_it_rejects_an_independent_program_without_its_own_platform(): void
    {
        $editor = User::factory()->create();
        $program = RadioProgram::factory()->make([
            'verified_by_user_id' => $editor->id,
            'verification_method' => 'manual',
            'verification_status' => 'verified',
            'last_verified_at' => now(),
        ]);

        $this->expectException(ValidationException::class);

        app(RadioDirectoryService::class)->saveProgram($program, [
            ...$program->toArray(),
            'editorial_status' => 'published',
        ]);
    }
}
