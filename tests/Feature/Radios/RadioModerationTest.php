<?php

namespace Tests\Feature\Radios;

use App\Models\RadioSignal;
use App\Models\User;
use App\Services\AdminAttentionService;
use App\Services\RadioDirectoryService;
use App\Support\ModerationQueue;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RadioModerationTest extends TestCase
{
    use DatabaseTransactions;

    private function admin(): User
    {
        Role::findOrCreate('administrador', 'web');
        $admin = User::factory()->create();
        $admin->assignRole('administrador');

        return $admin;
    }

    public function test_imported_draft_with_inactive_channels_can_be_edited(): void
    {
        $admin = $this->admin();
        $signal = RadioSignal::factory()->create(['created_by' => $admin->id]);
        $signal->listeningChannels()->create(['label' => 'Canal pendiente', 'channel_type' => 'website', 'platform' => 'sitio_web', 'url' => 'https://example.test/radio', 'is_active' => false, 'is_primary' => true, 'sort_order' => 0]);

        $this->withoutExceptionHandling()->actingAs($admin)
            ->get(route('backend.radios.signals.edit', $signal))
            ->assertOk()->assertSee('Canal pendiente')->assertSee('Guardar');
    }

    public function test_rejected_publication_redisplays_multiline_sources_without_a_server_error(): void
    {
        $admin = $this->admin();
        $signal = RadioSignal::factory()->create(['created_by' => $admin->id]);
        $url = route('backend.radios.signals.edit', $signal);
        $originalTitle = $signal->title;
        $channel = $signal->listeningChannels()->create(['label' => 'Sin verificar', 'channel_type' => 'website', 'platform' => 'sitio_web', 'url' => 'https://example.test/radio', 'is_active' => false, 'is_primary' => true, 'sort_order' => 0]);
        $this->actingAs($admin)->from($url)->put(route('backend.radios.signals.update', $signal), [
            'title' => 'Radio revisada', 'body' => '<p>Contenido revisado.</p>',
            'editorial_focus' => 'folklore', 'transmission_modes' => ['web'],
            'coverage_scope' => 'global', 'source_urls' => "https://example.test/uno\nhttps://example.test/dos",
            'editorial_status' => 'published', 'verification_status' => 'pending',
            'channels' => [['id' => $channel->id, 'label' => 'Canal revisado', 'channel_type' => 'website', 'platform' => 'sitio_web', 'url' => 'https://example.test/radio', 'is_active' => '0', 'is_primary' => '1']],
        ])->assertRedirect($url)->assertSessionHasErrors('editorial_status');

        $this->get($url)->assertOk()->assertSee('No se puede publicar')->assertSee('Radio revisada')
            ->assertSee('https://example.test/uno')->assertSee('https://example.test/dos');
        $this->assertSame('draft', $signal->fresh()->editorial_status);
        $this->assertSame($originalTitle, $signal->fresh()->title);
        $this->assertFalse($channel->fresh()->is_active);
    }

    public function test_invalid_creation_redisplays_sources_and_validation_errors(): void
    {
        $url = route('backend.radios.signals.create');
        $this->actingAs($this->admin())->from($url)->post(route('backend.radios.signals.store'), [
            'title' => '', 'body' => '<p>Descripción</p>', 'editorial_focus' => 'folklore',
            'transmission_modes' => ['web'], 'coverage_scope' => 'global',
            'source_urls' => 'https://example.test/fuente', 'verification_status' => 'pending', 'editorial_status' => 'draft',
        ])->assertRedirect($url)->assertSessionHasErrors('title');
        $this->get($url)->assertOk()->assertSee('https://example.test/fuente')->assertSee('No se pudo guardar');
    }

    public function test_drafts_appear_in_moderation_and_never_in_the_abm_even_with_an_explicit_filter(): void
    {
        $admin = $this->admin();
        $draft = RadioSignal::factory()->create(['title' => 'Pendiente exclusiva QA', 'created_by' => $admin->id]);
        $published = RadioSignal::factory()->create(['title' => 'Publicada exclusiva QA', 'editorial_status' => 'published']);
        foreach ([[], ['status' => 'draft']] as $filters) {
            $this->actingAs($admin)->get(route('backend.radios.signals.index', $filters))->assertOk()->assertDontSee($draft->title);
        }
        $this->get(route('backend.radios.signals.index'))->assertSee($published->title);
        $this->get(route('backend.moderation.index'))->assertOk()->assertSee('Radios en Borrador')
            ->assertSee($draft->title)->assertDontSee($published->title)
            ->assertSee(route('backend.radios.signals.edit', $draft), false)
            ->assertSee(route('backend.moderation.publish', ['type' => 'radio', 'id' => $draft->id]), false);
        $this->assertSame(ModerationQueue::radios()->count(), ModerationQueue::counts()['items']['Radios']);
    }

    public function test_moderation_reports_missing_verification_without_publishing(): void
    {
        $admin = $this->admin();
        $signal = RadioSignal::factory()->create();
        $url = route('backend.moderation.index');
        $this->actingAs($admin)->from($url)->post(route('backend.moderation.publish', ['type' => 'radio', 'id' => $signal->id]))
            ->assertRedirect($url)->assertSessionHasErrors('editorial_status')->assertSessionHas('open', 'radios');
        $this->get($url)->assertOk()->assertSee('No se puede publicar')->assertSee('current_verification');
        $this->assertSame('draft', $signal->fresh()->editorial_status);
        $this->assertNull($signal->fresh()->published_at);
    }

    public function test_a_verified_signal_is_published_from_moderation_and_invalidates_the_counter(): void
    {
        $admin = $this->admin();
        $signal = RadioSignal::factory()->create(['verification_status' => 'verified', 'last_verified_at' => now(), 'verification_method' => 'manual', 'verified_by_user_id' => $admin->id]);
        $signal->listeningChannels()->create(['label' => 'Escuchar', 'channel_type' => 'stream', 'url' => 'https://example.test/stream', 'is_active' => true, 'sort_order' => 0]);
        $attention = app(AdminAttentionService::class);
        $before = $attention->data()['review']['total'];
        $this->actingAs($admin)->post(route('backend.moderation.publish', ['type' => 'radio', 'id' => $signal->id]))
            ->assertRedirect(route('backend.moderation.index'))->assertSessionHas('open', 'radios');
        $this->assertSame('published', $signal->fresh()->editorial_status);
        $this->assertNotNull($signal->fresh()->published_at);
        $this->assertFalse(ModerationQueue::radios()->whereKey($signal->id)->exists());
        $this->assertSame($before - 1, $attention->data()['review']['total']);
        $this->get(route('backend.radios.signals.index'))->assertOk()->assertSee($signal->title);
        app(RadioDirectoryService::class)->unpublishSignal($signal->fresh());
        $this->assertSame($before, $attention->data()['review']['total']);
    }

    public function test_saving_a_draft_as_admin_returns_to_moderation(): void
    {
        $admin = $this->admin();
        $signal = RadioSignal::factory()->create();
        $this->actingAs($admin)->put(route('backend.radios.signals.update', $signal), [
            'title' => 'Título revisado', 'body' => '<p>Descripción revisada</p>', 'editorial_focus' => 'folklore',
            'transmission_modes' => ['web'], 'coverage_scope' => 'global', 'source_urls' => 'https://example.test/radio',
            'verification_status' => 'pending', 'editorial_status' => 'draft',
        ])->assertRedirect(route('backend.moderation.index'))->assertSessionHas('open', 'radios');
        $this->assertSame('Título revisado', $signal->fresh()->title);
        $this->assertSame('draft', $signal->fresh()->editorial_status);
    }

    public function test_a_collaborator_can_edit_own_draft_but_cannot_publish_from_moderation(): void
    {
        Role::findOrCreate('colaborador', 'web');
        $user = User::factory()->create();
        $user->assignRole('colaborador');
        $signal = RadioSignal::factory()->create(['created_by' => $user->id]);
        $this->actingAs($user)->get(route('backend.radios.signals.edit', $signal))->assertOk();
        $this->get(route('backend.moderation.index'))->assertForbidden();
        $this->post(route('backend.moderation.publish', ['type' => 'radio', 'id' => $signal->id]))->assertForbidden();
        $this->assertSame('draft', $signal->fresh()->editorial_status);
    }
}
