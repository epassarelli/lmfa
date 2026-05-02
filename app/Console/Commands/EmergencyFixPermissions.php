<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Artisan;

class EmergencyFixPermissions extends Command
{
    protected $signature = 'emergency:fix';
    protected $description = 'Crea los permisos faltantes y limpia caches para solucionar errores de carga';

    public function handle()
    {
        $this->info('Iniciando reparacion de emergencia...');

        // 1. Crear permiso faltante
        $permissionName = 'publish contents';
        $permission = Permission::firstOrCreate(['name' => $permissionName, 'guard_name' => 'web']);
        $this->info("Permiso '{$permissionName}' verificado/creado.");

        // 2. Asegurar que el Admin lo tenga
        $admin = Role::where('name', 'administrador')->first();
        if ($admin) {
            $admin->givePermissionTo($permission);
            $this->info("Permiso asignado al rol administrador.");
        }

        // 3. Permisos para Interpretes (Show/Edit/Delete)
        $interpretesPerms = ['access interprete', 'create interprete', 'read interprete', 'update interprete', 'delete interprete'];
        foreach ($interpretesPerms as $p) {
            Permission::firstOrCreate(['name' => $p, 'guard_name' => 'web']);
        }

        // 4. Limpiar caches de Spatie y Laravel
        Artisan::call('permission:cache-reset');
        Artisan::call('cache:clear');
        Artisan::call('view:clear');
        Artisan::call('route:clear');
        
        $this->info('Caches limpiadas y permisos sincronizados con exito.');
    }
}
