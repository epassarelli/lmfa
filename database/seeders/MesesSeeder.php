<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Mes;

class MesesSeeder extends Seeder
{
    public function run()
    {
        $meses = [
            'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
            'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'
        ];

        foreach ($meses as $mes) {
            Mes::create(['nombre' => $mes]);
        }
    }
}
