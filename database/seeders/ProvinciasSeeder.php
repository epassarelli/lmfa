<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Provincia;

class ProvinciasSeeder extends Seeder
{
    public function run()
    {
        $provincias = [
            'Buenos Aires', 'Catamarca', 'Chaco', 'Chubut', 'Córdoba', 'Corrientes', 'Entre Ríos',
            'Formosa', 'Jujuy', 'La Pampa', 'La Rioja', 'Mendoza', 'Misiones', 'Neuquén', 'Río Negro',
            'Salta', 'San Juan', 'San Luis', 'Santa Cruz', 'Santa Fe', 'Santiago del Estero',
            'Tierra del Fuego', 'Tucumán'
        ];

        foreach ($provincias as $provincia) {
            Provincia::create(['nombre' => $provincia]);
        }
    }
}
