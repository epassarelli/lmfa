<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Camino de confianza + baja del rol global "prensa".
 *
 * - Crea el permiso `publish contents` y el rol `verificado` (solo ese permiso). Se asigna ADEMAS de
 *   `colaborador`: quien lo tiene publica directo lo que puede escribir (lo propio o de sus fichas).
 * - "Prensa" se modela como relacion con una ficha (membresia), no como rol global: los usuarios con
 *   el rol `prensa` pasan a `colaborador` y el rol se elimina.
 */
return new class extends Migration
{
    /** Permisos que tenia el rol prensa, para poder recrearlo en down(). */
    private const PRENSA_PERMISSIONS = [
        'access noticia', 'create album', 'create cancion', 'create comida', 'create festival', 'create interprete',
        'create mito', 'create noticia', 'create show', 'read album', 'read cancion', 'read comida', 'read festival',
        'read interprete', 'read mito', 'read noticia', 'read show', 'update album', 'update cancion', 'update noticia',
        'update show',
    ];

    public function up(): void
    {
        DB::transaction(function () {
            $publish = Permission::findOrCreate('publish contents', 'web');
            Role::findOrCreate('verificado', 'web')->givePermissionTo($publish);

            $prensa = Role::where('name', 'prensa')->where('guard_name', 'web')->first();

            if ($prensa) {
                $colaborador = Role::findOrCreate('colaborador', 'web');

                foreach ($prensa->users()->get() as $user) {
                    $user->assignRole($colaborador);
                    $user->removeRole($prensa);
                }

                $prensa->delete();
            }
        });

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        DB::transaction(function () {
            $prensa = Role::findOrCreate('prensa', 'web');

            foreach (self::PRENSA_PERMISSIONS as $name) {
                $prensa->givePermissionTo(Permission::findOrCreate($name, 'web'));
            }

            Role::where('name', 'verificado')->where('guard_name', 'web')->first()?->delete();
            Permission::where('name', 'publish contents')->where('guard_name', 'web')->first()?->delete();
        });

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
