<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    public function run()
    {
        $categories = [
            ['name' => 'Eventos', 'slug' => 'eventos'],
            ['name' => 'Vendedores', 'slug' => 'vendedores'],
            ['name' => 'Servicios', 'slug' => 'servicios'],
            ['name' => 'Artistas', 'slug' => 'artistas'],
            ['name' => 'Instrumentos', 'slug' => 'instrumentos'],
            ['name' => 'Clases', 'slug' => 'clases'],
            ['name' => 'Otros', 'slug' => 'otros'],
        ];

        DB::table('categories')->insert($categories);
    }
}
