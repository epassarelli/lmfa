<?php

namespace Tests\Feature\Backend;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\File;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/** Lineamiento: todo formulario del backoffice lleva el encabezado estándar y los botones Guardar, Reset y Volver al listado. */
class FormHeaderAndActionsStandardTest extends TestCase
{
    use DatabaseTransactions;

    /** Pantallas que no son un alta/edición de una entidad (varios formularios o un flujo de envío). */
    private const EXCEPTIONS = ['account/edit.blade.php', 'newsletter/editions/edit.blade.php'];

    public function test_every_create_and_edit_view_uses_the_standard_header_and_actions(): void
    {
        $base = resource_path('views/backend');
        $files = collect(File::allFiles($base))
            ->map(fn ($file) => str_replace('\\', '/', $file->getRelativePathname()))
            ->filter(fn ($path) => preg_match('#(^|/)(create|edit)\.blade\.php$#', $path) || in_array($path, ['venues/form.blade.php', 'event-programs/form.blade.php']))
            ->reject(fn ($path) => in_array($path, self::EXCEPTIONS, true))
            // classifieds/create y edit solo incluyen classifieds/form, que es donde viven el encabezado y los botones.
            ->reject(fn ($path) => in_array($path, ['classifieds/create.blade.php', 'classifieds/edit.blade.php'], true))
            ->values();

        $this->assertGreaterThan(30, $files->count());

        foreach ($files as $path) {
            $source = File::get($base.'/'.$path);
            $this->assertStringContainsString('<x-backend.form-header', $source, "$path no usa el encabezado estándar.");
            $this->assertStringContainsString('<x-backend.form-actions', $source, "$path no usa los botones estándar.");
            $this->assertStringNotContainsString('<h1>', $source, "$path define su propio <h1>.");
        }

        $this->assertStringContainsString('x-backend.form-header', File::get($base.'/classifieds/form.blade.php'));
        $this->assertStringContainsString('x-backend.form-actions', File::get($base.'/classifieds/form.blade.php'));
    }

    public function test_the_components_render_the_expected_icons_and_buttons(): void
    {
        $create = (string) $this->render('<x-backend.form-header mode="create">Crear X</x-backend.form-header>');
        $this->assertStringContainsString('<h1><i class="fas fa-plus-circle mr-2"></i>Crear X</h1>', $create);

        $edit = (string) $this->render('<x-backend.form-header mode="edit">Editar X</x-backend.form-header>');
        $this->assertStringContainsString('<h1><i class="fas fa-edit mr-2"></i>Editar X</h1>', $edit);

        $actions = (string) $this->render('<x-backend.form-actions back="/listado" />');
        $this->assertStringContainsString('class="btn btn-primary"', $actions);
        $this->assertStringContainsString('fa-save', $actions);
        $this->assertStringContainsString('type="reset" class="btn btn-secondary"', $actions);
        $this->assertStringContainsString('fa-undo', $actions);
        $this->assertStringContainsString('href="/listado" class="btn btn-secondary"', $actions);
        $this->assertStringContainsString('Volver al listado', $actions);
    }

    public function test_create_screens_render_the_standard_header_and_buttons(): void
    {
        $admin = User::factory()->create();
        Role::findOrCreate('administrador', 'web');
        $admin->assignRole('administrador');

        $routes = [
            'backend.news.create', 'backend.events.create', 'backend.interpretes.create', 'backend.festivales.create', 'backend.comidas.create',
            'backend.discos.create', 'backend.canciones.create', 'backend.mitos.create', 'backend.penia-profiles.create',
            'backend.categories.create', 'backend.tags.create', 'backend.organizations.create', 'backend.knowledge-articles.create',
            'backend.venues.create', 'backend.classifieds.create', 'backend.radios.programs.create', 'backend.radios.signals.create',
            'users.create', 'roles.create', 'permissions.create',
        ];

        foreach ($routes as $route) {
            $html = $this->actingAs($admin)->get(route($route))->assertOk()->getContent();

            $this->assertStringContainsString('fa-plus-circle mr-2', $html, "$route sin ícono de crear.");
            $this->assertStringContainsString('<i class="fas fa-save"></i> Guardar', $html, "$route sin botón Guardar.");
            $this->assertStringContainsString('<i class="fas fa-undo"></i> Reset', $html, "$route sin botón Reset.");
            $this->assertStringContainsString('<i class="fas fa-arrow-left"></i> Volver al listado', $html, "$route sin botón Volver al listado.");
        }
    }

    public function test_edit_screens_show_the_edit_icon_and_the_three_buttons(): void
    {
        $admin = User::factory()->create();
        Role::findOrCreate('administrador', 'web');
        $admin->assignRole('administrador');
        $role = Role::findOrCreate('rol-estandar-qa', 'web');
        $permission = \Spatie\Permission\Models\Permission::findOrCreate('permiso estandar qa', 'web');

        foreach ([route('roles.edit', $role->id) => 'rol-estandar-qa', route('permissions.edit', $permission->id) => 'permiso estandar qa'] as $url => $name) {
            $html = $this->actingAs($admin)->get($url)->assertOk()->getContent();

            $this->assertStringContainsString('fa-edit mr-2', $html);
            $this->assertStringContainsString($name, $html);
            $this->assertStringContainsString('<i class="fas fa-save"></i> Guardar', $html);
            $this->assertStringContainsString('<i class="fas fa-undo"></i> Reset', $html);
            $this->assertStringContainsString('<i class="fas fa-arrow-left"></i> Volver al listado', $html);
        }
    }

    private function render(string $template): string
    {
        return \Illuminate\Support\Facades\Blade::render($template);
    }
}
