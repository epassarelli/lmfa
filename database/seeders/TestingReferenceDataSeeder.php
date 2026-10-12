<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Datos de referencia que la suite de tests da por existentes: roles y permisos tal como están
 * hoy en la base real (administrador, colaborador, verificado). Idempotente. Solo para bases de
 * test (CI y mfa_test); no reemplaza a RolesAndPermissionsSeeder, que es histórico y está desfasado.
 */
class TestingReferenceDataSeeder extends Seeder
{
    private const CONTRIBUTOR_ACTIONS = [
        'access noticia',
        'create album', 'create cancion', 'create comida', 'create festival', 'create interprete', 'create mito', 'create noticia', 'create show',
        'read album', 'read cancion', 'read comida', 'read festival', 'read interprete', 'read mito', 'read noticia', 'read show',
    ];

    public function run(): void
    {
        if (app()->environment('production')) {
            $this->command?->error('TestingReferenceDataSeeder no se ejecuta en producción.');

            return;
        }

        $entities = ['user', 'interprete', 'noticia', 'show', 'cancion', 'album', 'festival', 'mito', 'comida'];
        $permissions = ['access noticia', 'access user', 'publish contents'];

        foreach ($entities as $entity) {
            foreach (['create', 'read', 'update', 'delete'] as $action) {
                $permissions[] = "$action $entity";
            }
        }

        foreach (array_unique($permissions) as $name) {
            Permission::findOrCreate($name, 'web');
        }

        Role::findOrCreate('administrador', 'web')->syncPermissions(Permission::where('guard_name', 'web')->pluck('name')->all());
        Role::findOrCreate('colaborador', 'web')->syncPermissions(self::CONTRIBUTOR_ACTIONS);
        Role::findOrCreate('verificado', 'web')->syncPermissions(['publish contents']);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
