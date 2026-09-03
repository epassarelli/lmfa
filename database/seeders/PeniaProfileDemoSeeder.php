<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * @deprecated Conservado como alias para no romper runbooks anteriores.
 */
class PeniaProfileDemoSeeder extends Seeder
{
    public function run(): void
    {
        $this->command?->warn('PeniaProfileDemoSeeder fue reemplazado por PeniaProfilePilotSeeder.');
        $this->call(PeniaProfilePilotSeeder::class);
    }
}
