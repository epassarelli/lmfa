<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class AssignRolesToUsersSeeder extends Seeder
{
    public function run()
    {
        // Asignar rol 'administrador' al usuario con ID 1
        $user1 = User::find(1);
        if ($user1) {
            $user1->assignRole('administrador');
        } else {
            $this->command->info('User with ID 1 not found.');
        }

        // Asignar rol 'prensa' al usuario con ID 2
        $user2 = User::find(2);
        if ($user2) {
            $user2->assignRole('prensa');
        } else {
            $this->command->info('User with ID 2 not found.');
        }

        // Asignar rol 'colaborador' al usuario con ID 437
        $user437 = User::find(437);
        if ($user437) {
            $user437->assignRole('colaborador');
        } else {
            $this->command->info('User with ID 437 not found.');
        }
    }
}
