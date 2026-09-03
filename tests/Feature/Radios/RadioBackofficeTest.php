<?php

namespace Tests\Feature\Radios;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RadioBackofficeTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        Role::findOrCreate('administrador', 'web');
        Role::findOrCreate('colaborador', 'web');
    }

    public function test_a_guest_cannot_access_the_radio_backoffice(): void
    {
        $this->get(route('backend.radios.signals.index'))->assertRedirect(route('login'));
    }

    public function test_a_collaborator_can_propose_but_not_verify_or_publish_a_streaming_signal(): void
    {
        $user = User::factory()->create();
        $user->assignRole('colaborador');

        $response = $this->actingAs($user)->post(route('backend.radios.signals.store'), [
            'title' => 'Radio de prueba streaming',
            'body' => '<p>Programación folklórica.</p>',
            'editorial_focus' => 'folklore',
            'transmission_modes' => ['streaming'],
            'coverage_scope' => 'national',
            'source_urls' => ['https://example.test/radio'],
            'verification_status' => 'verified',
            'last_verified_at' => now()->format('Y-m-d H:i:s'),
            'verified_by_user_id' => $user->id,
            'verification_method' => 'manual',
            'editorial_status' => 'published',
            'channels' => [[
                'label' => 'Escuchar en vivo',
                'channel_type' => 'stream',
                'platform' => 'stream_directo',
                'url' => 'https://example.test/escuchar',
                'is_active' => true,
            ]],
        ]);

        $response->assertRedirect(route('backend.radios.signals.index'));
        $signal = \App\Models\RadioSignal::where('title', 'Radio de prueba streaming')->firstOrFail();
        $this->assertSame('draft', $signal->editorial_status);
        $this->assertSame('pending', $signal->verification_status);
        $this->assertNull($signal->verified_by_user_id);
        $this->assertNull($signal->last_verified_at);
        $this->assertNull($signal->verification_method);

        $this->actingAs($user)
            ->post(route('backend.radios.signals.publish', $signal))
            ->assertForbidden();
    }

    public function test_a_collaborator_can_create_an_independent_program(): void
    {
        $user = User::factory()->create();
        $user->assignRole('colaborador');

        $response = $this->actingAs($user)->post(route('backend.radios.programs.store'), [
            'title' => 'Ronda independiente',
            'body' => '<p>Programa de folklore.</p>',
            'is_folklore' => true,
            'platform' => 'youtube',
            'listening_url' => 'https://youtube.com/@ejemplo/live',
            'source_urls' => ['https://example.test/programa'],
            'verification_status' => 'verified',
            'last_verified_at' => now()->format('Y-m-d H:i:s'),
            'verified_by_user_id' => $user->id,
            'verification_method' => 'manual',
            'editorial_status' => 'published',
            'slots' => [['weekday' => 1, 'starts_at' => '20:00', 'ends_at' => '22:00']],
        ]);

        $response->assertRedirect(route('backend.radios.programs.index'));
        $program = \App\Models\RadioProgram::where('title', 'Ronda independiente')->firstOrFail();
        $this->assertSame('draft', $program->editorial_status);
        $this->assertSame('pending', $program->verification_status);
        $this->assertNull($program->verified_by_user_id);

        $this->actingAs($user)
            ->post(route('backend.radios.programs.publish', $program))
            ->assertForbidden();
    }
}
