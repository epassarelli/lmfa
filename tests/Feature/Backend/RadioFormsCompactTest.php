<?php

namespace Tests\Feature\Backend;

use App\Models\RadioProgram;
use App\Models\RadioSignal;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/** Emisoras (señales) y Programas de radio: grilla de 12, controles compactos y ayudas en tooltip. */
class RadioFormsCompactTest extends TestCase
{
    use DatabaseTransactions;

    private function user(string $role): User
    {
        $user = User::factory()->create();
        Role::findOrCreate($role, 'web');
        $user->assignRole($role);

        return $user;
    }

    private function assertCompact(string $html): void
    {
        $this->assertStringContainsString('class="form-compact"', $html);
        $this->assertStringContainsString('data-toggle="tooltip"', $html);
        $this->assertStringContainsString('col-md-12 col-xl-4', $html);
        $this->assertStringNotContainsString('<h5 class="mb-3">', $html);
        // Obligatorios con asterisco en la etiqueta y atributo required.
        $this->assertMatchesRegularExpression('/<input id="title"[^>]*\srequired/', $html);
        $this->assertMatchesRegularExpression('/<textarea id="body"[^>]*\srequired/', $html);
        $this->assertMatchesRegularExpression('/<textarea id="source_urls"[^>]*\srequired/', $html);
        // El slug es opcional: el servidor lo genera desde el nombre.
        $this->assertDoesNotMatchRegularExpression('/<input id="slug"[^>]*\srequired/', $html);
    }

    public function test_the_signal_form_uses_the_compact_grid_and_keeps_the_channels_script_hooks(): void
    {
        $admin = $this->user('administrador');
        $signal = RadioSignal::factory()->create();

        foreach ([route('backend.radios.signals.create'), route('backend.radios.signals.edit', $signal)] as $url) {
            $html = $this->actingAs($admin)->get($url)->assertOk()->getContent();

            $this->assertCompact($html);
            $this->assertMatchesRegularExpression('/id="title".*id="slug".*id="editorial_focus".*id="excerpt".*id="body".*id="radio-province".*id="radio-locality".*id="city".*id="coverage_scope".*id="address".*id="latitude".*id="longitude".*id="coverage_notes".*id="phone".*id="email".*id="website".*id="source_urls".*id="featured_image_path".*id="image_alt".*id="seo_title".*id="editorial_status"/s', $html);
            // Ganchos que usa el JS de canales y el filtro de localidades.
            foreach (['id="add-radio-channel"', 'id="radio-channels"', 'id="radio-channel-template"', 'data-primary-channel', 'data-remove-channel', 'channels['] as $hook) {
                $this->assertStringContainsString($hook, $html);
            }
        }
    }

    public function test_the_program_form_uses_the_compact_grid_and_keeps_the_slots_script_hooks(): void
    {
        $admin = $this->user('administrador');
        $program = RadioProgram::factory()->create();

        foreach ([route('backend.radios.programs.create'), route('backend.radios.programs.edit', $program)] as $url) {
            $html = $this->actingAs($admin)->get($url)->assertOk()->getContent();

            $this->assertCompact($html);
            $this->assertMatchesRegularExpression('/id="title".*id="slug".*id="radio_signal_id".*id="excerpt".*id="body".*id="platform".*id="listening_url".*id="source_urls".*id="seo_title".*id="editorial_status"/s', $html);
            foreach (['id="add-program-slot"', 'id="program-slots"', 'id="program-slot-template"', 'data-remove-slot', 'slots['] as $hook) {
                $this->assertStringContainsString($hook, $html);
            }
        }
    }

    public function test_a_collaborator_sees_the_pending_notice_instead_of_the_editorial_control(): void
    {
        $html = $this->actingAs($this->user('colaborador'))->get(route('backend.radios.signals.create'))->assertOk()->getContent();

        $this->assertStringContainsString('Tu aporte se guardará como borrador pendiente', $html);
        $this->assertStringNotContainsString('id="editorial_status"', $html);
    }
}
