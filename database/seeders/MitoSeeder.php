<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class MitoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \App\Models\Mito::factory(100)->create();
    }
}
