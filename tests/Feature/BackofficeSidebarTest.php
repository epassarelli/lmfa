<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use JeroenNoten\LaravelAdminLte\Menu\Filters\GateFilter;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class BackofficeSidebarTest extends TestCase
{
    use DatabaseTransactions;

    public function test_menu_structure_and_alphabetical_order_are_preserved(): void
    {
        $menu = config('adminlte.menu');
        $links = array_values(array_filter($menu, fn ($item) => isset($item['text'])));
        $byText = collect($links)->keyBy('text');
        $this->assertFalse($byText->has('Música y Cultura'));
        foreach (['Discos', 'Letras / Canciones', 'Festivales', 'Peñas', 'Comidas Típicas', 'Mitos y Leyendas'] as $text) {
            $this->assertTrue($byText->has($text));
            $this->assertArrayHasKey('url', $byText[$text]);
        }
        $this->assertSame(['Cuentas Sociales', 'Historial de Publicación', 'Nueva Publicación', 'Plantillas (Templates)'], array_column($byText['Pasarela de Contenidos']['submenu'], 'text'));
        $this->assertSame(['Permisos', 'Roles', 'Usuarios'], array_column($byText['Usuarios y Roles']['submenu'], 'text'));
        $sections = [];
        $current = [];
        foreach ($menu as $item) {
            if (isset($item['header'])) {
                $sections[] = $current;
                $current = [];
            } else {
                $current[] = $item;
            }
        }
        $sections[] = $current;
        foreach ($sections as $items) {
            $this->assertOrdered($items);
        }
    }

    private function assertOrdered(array $items): void
    {
        $labels = array_map(fn ($item) => Str::ascii(mb_strtolower($item['text'])), $items);
        $expected = $labels;
        sort($expected, SORT_STRING);
        $this->assertSame($expected, $labels);
        foreach ($items as $item) {
            if (isset($item['submenu'])) {
                $this->assertOrdered($item['submenu']);
            }
        }
    }

    public function test_grouped_pasarela_keeps_link_permissions(): void
    {
        Role::findOrCreate('administrador', 'web');
        $admin = User::factory()->create();
        $admin->assignRole('administrador');
        $this->actingAs($admin)->get(route('backend.events.create'))->assertOk()
            ->assertSee('Pasarela de Contenidos')->assertDontSee('Música y Cultura');
        $group = collect(config('adminlte.menu'))->firstWhere('text', 'Pasarela de Contenidos');
        $this->assertSame(['manage social accounts', 'manage user', 'manage user', 'manage templates'], array_column($group['submenu'], 'can'));
        $user = User::factory()->create();
        $this->actingAs($user);
        $filter = app(GateFilter::class);
        foreach ($group['submenu'] as $link) {
            $this->assertTrue($filter->transform($link)['restricted']);
        }
    }
}
