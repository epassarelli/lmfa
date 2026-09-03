<?php

namespace Tests\Feature\Radios;

use App\Models\RadioListeningChannel;
use App\Models\RadioProgram;
use App\Models\RadioSignal;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class RadioApiTest extends TestCase
{
    use DatabaseTransactions;

    public function test_public_signal_index_only_returns_published_and_verified_records(): void
    {
        $editor = User::factory()->create();
        $visible = RadioSignal::factory()->create([
            'title' => 'Radio visible API',
            'editorial_status' => 'published',
            'published_at' => now(),
            'verification_status' => 'verified',
            'last_verified_at' => now(),
            'verified_by_user_id' => $editor->id,
            'verification_method' => 'manual',
        ]);
        RadioListeningChannel::factory()->create(['radio_signal_id' => $visible->id]);
        RadioSignal::factory()->create(['title' => 'Radio borrador API']);

        $this->actingAs($editor, 'sanctum')->getJson(route('radio-signals.index'))
            ->assertOk()
            ->assertJsonFragment(['title' => 'Radio visible API'])
            ->assertJsonMissing(['title' => 'Radio borrador API']);
    }

    public function test_public_program_show_returns_active_schedule(): void
    {
        $editor = User::factory()->create();
        $program = RadioProgram::factory()->create([
            'title' => 'Programa visible API',
            'editorial_status' => 'published',
            'published_at' => now(),
            'verification_status' => 'verified',
            'last_verified_at' => now(),
            'verified_by_user_id' => $editor->id,
            'verification_method' => 'manual',
            'platform' => 'youtube',
            'listening_url' => 'https://youtube.com/@ejemplo/live',
        ]);
        $program->slots()->create(['weekday' => 1, 'starts_at' => '20:00', 'timezone' => 'America/Argentina/Buenos_Aires']);

        $this->actingAs($editor, 'sanctum')->getJson(route('radio-programs.show', $program))
            ->assertOk()
            ->assertJsonPath('title', 'Programa visible API')
            ->assertJsonCount(1, 'slots');
    }
}
