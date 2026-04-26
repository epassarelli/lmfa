<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run()
    {
        // Definir permisos para cada entidad
        $entities = ['user', 'interprete', 'noticia', 'show', 'cancion', 'album', 'festival', 'mito', 'comida'];
        $actions = ['create', 'read', 'update', 'delete'];

        foreach ($entities as $entity) {
            // Crear permisos de acceso CRUD
            Permission::create(['name' => "access $entity"]);

            // Crear permisos individuales
            foreach ($actions as $action) {
                Permission::create(['name' => "$action $entity"]);
            }
        }

        // Permisos de la Pasarela de Contenidos
        Permission::create(['name' => 'publish contents']);
        Permission::create(['name' => 'manage social accounts']);
        Permission::create(['name' => 'manage templates']);

        // Crear roles
        $admin = Role::create(['name' => 'administrador']);
        $prensa = Role::create(['name' => 'prensa']);
        $colaborador = Role::create(['name' => 'colaborador']);

        // Asignar todos los permisos al administrador
        $admin->givePermissionTo(Permission::all());

        // Permisos para el rol Prensa
        $prensaPermissions = [
            'interprete' => ['access', 'create', 'read'],
            'noticia' => ['access', 'create', 'read', 'update'],
            'show' => ['access', 'create', 'read', 'update'],
            'album' => ['access', 'create', 'read', 'update'],
            'cancion' => ['access', 'create', 'read', 'update'],
            'festival' => ['access', 'create', 'read'],
            'mito' => ['access', 'create', 'read'],
            'comida' => ['access', 'create', 'read'],
        ];

        foreach ($prensaPermissions as $entity => $actions) {
            foreach ($actions as $action) {
                $prensa->givePermissionTo("$action $entity");
            }
        }

        // Permisos adicionales para Prensa (Redes y Publicar)
        $prensa->givePermissionTo(['publish contents', 'manage social accounts', 'read user']);

        // Permisos para el rol Colaborador
        $colaboradorPermissions = [
            'interprete' => ['access', 'create', 'read'],
            'noticia' => ['access', 'create', 'read'],
            'show' => ['access', 'create', 'read'],
            'album' => ['access', 'create', 'read'],
            'cancion' => ['access', 'create', 'read'],
            'festival' => ['access', 'create', 'read'],
            'mito' => ['access', 'create', 'read'],
            'comida' => ['access', 'create', 'read'],
        ];

        foreach ($colaboradorPermissions as $entity => $actions) {
            foreach ($actions as $action) {
                $colaborador->givePermissionTo("$action $entity");
            }
        }
    }
}
