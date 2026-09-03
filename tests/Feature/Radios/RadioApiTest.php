<?php

namespace Tests\Feature\Radios;

use App\Models\RadioListeningChannel;
use App\Models\RadioProgram;
use App\Models\RadioSignal;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RadioApiTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        Role::findOrCreate('administrador', 'web');
    }

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

    public function test_content_refresh_creates_draft_radio_resources_and_allows_partial_updates(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('administrador');
        Sanctum::actingAs($admin);

        $signalResponse = $this->postJson('/api/v1/radio-signals', [
            'title' => 'Radio Content Refresh',
            'slug' => 'radio-content-refresh',
            'body' => '<p>Ficha editorial de la radio.</p>',
            'editorial_focus' => 'folklore',
            'transmission_modes' => ['streaming'],
            'coverage_scope' => 'national',
            'source_urls' => ['https://example.test/radio-refresh'],
            'verification_status' => 'pending',
            'editorial_status' => 'draft',
            'channels' => [[
                'label' => 'Escuchar en vivo',
                'channel_type' => 'stream',
                'platform' => 'stream_directo',
                'url' => 'https://example.test/radio-refresh/vivo',
                'is_primary' => true,
                'is_active' => true,
            ]],
        ])->assertCreated()
            ->assertJsonPath('editorial_status', 'draft')
            ->assertJsonPath('verification_status', 'pending');

        $signalId = $signalResponse->json('id');

        $this->putJson("/api/v1/radio-signals/{$signalId}", [
            'seo_title' => 'Radio Content Refresh actualizada',
        ])->assertOk()
            ->assertJsonPath('seo_title', 'Radio Content Refresh actualizada')
            ->assertJsonPath('editorial_status', 'draft')
            ->assertJsonCount(1, 'listening_channels');

        $programResponse = $this->postJson('/api/v1/radio-programs', [
            'radio_signal_id' => $signalId,
            'title' => 'Programa Content Refresh',
            'slug' => 'programa-content-refresh',
            'body' => '<p>Ficha editorial del programa.</p>',
            'is_folklore' => true,
            'source_urls' => ['https://example.test/programa-refresh'],
            'verification_status' => 'pending',
            'editorial_status' => 'draft',
            'slots' => [[
                'weekday' => 1,
                'starts_at' => '20:00',
                'ends_at' => '22:00',
                'timezone' => 'America/Argentina/Buenos_Aires',
                'is_active' => true,
            ]],
        ])->assertCreated()
            ->assertJsonPath('editorial_status', 'draft')
            ->assertJsonPath('verification_status', 'pending');

        $programId = $programResponse->json('id');

        $this->putJson("/api/v1/radio-programs/{$programId}", [
            'meta_description' => 'Programa actualizado desde Content Refresh.',
        ])->assertOk()
            ->assertJsonPath('meta_description', 'Programa actualizado desde Content Refresh.')
            ->assertJsonPath('editorial_status', 'draft')
            ->assertJsonPath('radio_signal_id', $signalId)
            ->assertJsonCount(1, 'slots');
    }
}
