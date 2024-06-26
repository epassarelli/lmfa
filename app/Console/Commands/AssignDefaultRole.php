<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Spatie\Permission\Models\Role;

class AssignDefaultRole extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:assign-default-role';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $defaultRole = Role::find(3);
        $users = User::doesntHave('roles')->get();

        foreach ($users as $user) {
            $user->assignRole($defaultRole);
        }

        $this->info('Roles asignados a los usuarios sin roles.');
    }
}
