<?php

namespace Tests\Feature\Backend;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/** El ABM de Radios (emisoras/señales y programas) tiene que estar en el menú lateral del backoffice. */
class RadiosSidebarEntryTest extends TestCase
{
    use DatabaseTransactions;

    private function user(string $role): User
    {
        $user = User::factory()->create();
        Role::findOrCreate($role, 'web');
        $user->assignRole($role);

        return $user;
    }

    public function test_the_menu_config_lists_radios_with_signals_and_programs(): void
    {
        $radios = collect(config('adminlte.menu'))->firstWhere('text', 'Radios');

        $this->assertNotNull($radios, 'Falta la entrada Radios en el menú.');
        $this->assertSame('backoffice-section.radios', $radios['can']);
        $this->assertSame(['admin/radios/signals', 'admin/radios/programs'], array_column($radios['submenu'], 'url'));
        $this->assertArrayHasKey('radios', config('backoffice_access.sections'));
    }

    public function test_an_administrator_sees_both_radio_links_and_they_open(): void
    {
        $admin = $this->user('administrador');

        $html = $this->actingAs($admin)->get(route('backend.events.create'))->assertOk()->getContent();
        $this->assertStringContainsString(url('admin/radios/signals'), $html);
        $this->assertStringContainsString(url('admin/radios/programs'), $html);
        $this->assertStringContainsString('Emisoras (señales)', $html);

        $this->actingAs($admin)->get(route('backend.radios.signals.index'))->assertOk();
        $this->actingAs($admin)->get(route('backend.radios.programs.index'))->assertOk();
    }

    public function test_with_access_by_entities_on_a_collaborator_does_not_see_the_radios_menu(): void
    {
        config(['backoffice_access.enabled' => true]);

        $this->actingAs($this->user('colaborador'))->get(route('dashboard'))->assertOk()
            ->assertDontSee(url('admin/radios/signals'));
    }

    public function test_with_access_by_entities_on_an_administrator_keeps_the_radios_menu(): void
    {
        config(['backoffice_access.enabled' => true]);

        $this->actingAs($this->user('administrador'))->get(route('backend.events.create'))->assertOk()
            ->assertSee(url('admin/radios/signals'));
    }
}
