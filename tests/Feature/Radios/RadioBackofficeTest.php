<?php

namespace Tests\Feature\Radios;

use App\Models\Locality;
use App\Models\Provincia;
use App\Models\RadioProgram;
use App\Models\RadioSignal;
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

    public function test_an_admin_can_manage_the_complete_signal_and_program_contract(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('administrador');
        $province = Provincia::query()->firstOrCreate(['nombre' => 'Córdoba']);
        $locality = Locality::query()->firstOrCreate(['province_id' => $province->id, 'name' => 'Cosquín']);

        $this->actingAs($admin)->get(route('backend.radios.signals.create'))
            ->assertOk()->assertSee('featured_image_path', false)->assertSee('channels[', false)->assertSee('coverage_notes', false);

        $this->actingAs($admin)->post(route('backend.radios.signals.store'), [
            'title' => 'Radio completa de prueba', 'slug' => 'radio-completa-de-prueba',
            'excerpt' => 'Señal folklórica de prueba.', 'body' => '<h2>Historia</h2><p>Contenido completo.</p>',
            'editorial_focus' => 'folklore', 'transmission_modes' => ['air', 'streaming'],
            'province_id' => $province->id, 'locality_id' => $locality->id, 'city' => 'Cosquín',
            'address' => 'San Martín 100', 'latitude' => -31.24, 'longitude' => -64.46,
            'coverage_scope' => 'regional', 'coverage_notes' => 'Valle de Punilla',
            'phone' => '+54 3541 000000', 'email' => 'radio@example.test', 'website' => 'https://example.test/radio',
            'featured_image_path' => 'radios/radio-completa.webp', 'image_alt' => 'Estudio de Radio completa',
            'source_urls' => ['https://example.test/radio/fuente'],
            'seo_title' => 'Radio completa de folklore', 'meta_description' => 'Señal completa de folklore para validar el backoffice.',
            'verification_status' => 'verified', 'last_verified_at' => now()->format('Y-m-d H:i:s'),
            'verified_by_user_id' => $admin->id, 'verification_method' => 'official_source',
            'editorial_status' => 'published',
            'channels' => [
                ['label' => 'FM 98.7', 'channel_type' => 'frequency', 'frequency_band' => 'FM', 'frequency' => '98.7', 'is_primary' => true, 'is_active' => true],
                ['label' => 'Escuchar', 'channel_type' => 'stream', 'platform' => 'stream_directo', 'url' => 'https://example.test/radio/vivo', 'is_primary' => true, 'is_active' => true],
            ],
        ])->assertRedirect(route('backend.radios.signals.index'));

        $signal = RadioSignal::query()->where('slug', 'radio-completa-de-prueba')->firstOrFail();
        $this->assertSame('published', $signal->editorial_status);
        $this->assertSame($locality->id, $signal->locality_id);
        $this->assertSame(2, $signal->listeningChannels()->count());
        $this->assertSame(1, $signal->listeningChannels()->where('is_primary', true)->count());

        $this->actingAs($admin)->get(route('backend.radios.signals.preview', $signal))
            ->assertOk()->assertSee('noindex,nofollow', false)->assertSee('RadioStation');

        $this->actingAs($admin)->get(route('backend.radios.programs.create'))
            ->assertOk()->assertSee('slots[', false)->assertSee('listening_url', false)->assertSee('seo_title', false);
        $this->actingAs($admin)->post(route('backend.radios.programs.store'), [
            'radio_signal_id' => $signal->id, 'title' => 'Programa completo de prueba', 'slug' => 'programa-completo-de-prueba',
            'excerpt' => 'Programa semanal de folklore.', 'body' => '<h2>Propuesta</h2><p>Contenido completo.</p>',
            'is_folklore' => true, 'source_urls' => ['https://example.test/programa/fuente'],
            'seo_title' => 'Programa completo de folklore', 'meta_description' => 'Grilla y escucha del programa completo de prueba.',
            'verification_status' => 'verified', 'last_verified_at' => now()->format('Y-m-d H:i:s'),
            'verified_by_user_id' => $admin->id, 'verification_method' => 'official_source',
            'editorial_status' => 'published',
            'slots' => [['weekday' => 1, 'starts_at' => '20:00', 'ends_at' => '22:00', 'timezone' => 'America/Argentina/Buenos_Aires', 'is_active' => true]],
        ])->assertRedirect(route('backend.radios.programs.index'));

        $program = RadioProgram::query()->where('slug', 'programa-completo-de-prueba')->firstOrFail();
        $this->assertSame('published', $program->editorial_status);
        $this->assertSame(1, $program->slots()->count());
        $this->actingAs($admin)->get(route('backend.radios.programs.preview', $program))
            ->assertOk()->assertSee('noindex,nofollow', false)->assertSee('RadioSeries');

        $this->actingAs($admin)->post(route('backend.radios.programs.unpublish', $program))->assertRedirect();
        $this->actingAs($admin)->post(route('backend.radios.signals.unpublish', $signal))->assertRedirect();
        $this->assertSame('draft', $program->fresh()->editorial_status);
        $this->assertSame('draft', $signal->fresh()->editorial_status);
    }
}
