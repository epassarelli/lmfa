<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TagSeeder extends Seeder
{
    public function run()
    {
        $tags = [
            ['name' => 'Folklore', 'slug' => 'folklore'],
            ['name' => 'Tango', 'slug' => 'tango'],
            ['name' => 'Guitarra', 'slug' => 'guitarra'],
            ['name' => 'Violín', 'slug' => 'violin'],
            ['name' => 'Bombo', 'slug' => 'bombo'],
            ['name' => 'Clases', 'slug' => 'clases'],
            ['name' => 'Eventos', 'slug' => 'eventos'],
            ['name' => 'Instrumentos', 'slug' => 'instrumentos'],
            ['name' => 'Servicios', 'slug' => 'servicios'],
            ['name' => 'Artistas', 'slug' => 'artistas'],
        ];

        DB::table('tags')->insert($tags);
    }
}
