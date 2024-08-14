<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ClassifiedSeeder extends Seeder
{
    public function run()
    {
        $now = Carbon::now();

        $categories = [
            'Eventos' => [
                ['title' => 'Festival de Folklore en La Plata', 'description' => 'Gran festival con artistas locales y nacionales.', 'price' => '0', 'location' => 'La Plata', 'contact_info' => 'eventos@festival.com', 'expiration_date' => $now->copy()->addMonth()],
                ['title' => 'Concierto de Tango en Buenos Aires', 'description' => 'Concierto especial de tango con músicos reconocidos.', 'price' => '5000', 'location' => 'Buenos Aires', 'contact_info' => 'tango@concierto.com', 'expiration_date' => $now->copy()->addMonth()],
                ['title' => 'Encuentro de Folklore en Mendoza', 'description' => 'Encuentro de folklore con talleres y presentaciones.', 'price' => '0', 'location' => 'Mendoza', 'contact_info' => 'encuentro@folklore.com', 'expiration_date' => $now->copy()->addWeeks(2)],
            ],
            'Vendedores' => [
                ['title' => 'Vendo Guitarra Acústica', 'description' => 'Guitarra acústica en excelente estado, usada solo en casa. Incluye estuche.', 'price' => '15000', 'location' => 'Buenos Aires', 'contact_info' => 'guitarras@vende.com', 'expiration_date' => $now->copy()->addMonth()],
                ['title' => 'Vendo Bombos Legüeros', 'description' => 'Bombos legüeros a buen precio, ideales para folklore.', 'price' => '25000', 'location' => 'CABA', 'contact_info' => 'bombos@vende.com', 'expiration_date' => $now->copy()->addMonth()],
                ['title' => 'Vendo Violín', 'description' => 'Violín en muy buen estado, con estuche y arco.', 'price' => '20000', 'location' => 'Rosario', 'contact_info' => 'violines@vende.com', 'expiration_date' => $now->copy()->addMonth()],
            ],
            'Servicios' => [
                ['title' => 'Servicios de Sonido para Eventos', 'description' => 'Ofrecemos servicios de sonido profesional para eventos. Contamos con equipo de última tecnología.', 'price' => '50000', 'location' => 'CABA', 'contact_info' => 'sonido@servicios.com', 'expiration_date' => $now->copy()->addMonth()],
                ['title' => 'Clases de Guitarra para Todos los Niveles', 'description' => 'Clases de guitarra impartidas por músicos profesionales, todos los niveles.', 'price' => '3000 por mes', 'location' => 'San Fernando', 'contact_info' => 'clases@guitarra.com', 'expiration_date' => $now->copy()->addMonth()],
                ['title' => 'Arreglo de Instrumentos Musicales', 'description' => 'Reparación y mantenimiento de instrumentos musicales.', 'price' => 'A consultar', 'location' => 'La Plata', 'contact_info' => 'reparaciones@instrumentos.com', 'expiration_date' => $now->copy()->addMonth()],
            ],
            'Artistas' => [
                ['title' => 'Busco Banda para Proyecto Musical', 'description' => 'Busco músicos para formar una banda de folklore.', 'price' => '0', 'location' => 'CABA', 'contact_info' => 'banda@proyecto.com', 'expiration_date' => $now->copy()->addMonth()],
                ['title' => 'Se Busca Corista para Conciertos', 'description' => 'Se busca corista con experiencia para participar en conciertos.', 'price' => 'A convenir', 'location' => 'Buenos Aires', 'contact_info' => 'corista@conciertos.com', 'expiration_date' => $now->copy()->addMonth()],
                ['title' => 'Artista Folklórico Busca Gira', 'description' => 'Artista folklórico busca fechas para gira nacional.', 'price' => '0', 'location' => 'Mendoza', 'contact_info' => 'gira@artista.com', 'expiration_date' => $now->copy()->addMonth()],
            ],
            'Instrumentos' => [
                ['title' => 'Guitarra Clásica', 'description' => 'Guitarra clásica en excelente estado, usada poco.', 'price' => '18000', 'location' => 'CABA', 'contact_info' => 'guitarras@instrumentos.com', 'expiration_date' => $now->copy()->addMonth()],
                ['title' => 'Bandoneón', 'description' => 'Bandoneón en muy buen estado, ideal para tango.', 'price' => '35000', 'location' => 'Buenos Aires', 'contact_info' => 'bandoneon@instrumentos.com', 'expiration_date' => $now->copy()->addMonth()],
                ['title' => 'Flauta Dulce', 'description' => 'Flauta dulce en perfecto estado, con estuche.', 'price' => '8000', 'location' => 'La Plata', 'contact_info' => 'flauta@instrumentos.com', 'expiration_date' => $now->copy()->addMonth()],
            ],
            'Clases' => [
                ['title' => 'Clases de Canto Folklórico', 'description' => 'Clases de canto folklórico con profesor experimentado.', 'price' => '3500 por mes', 'location' => 'San Isidro', 'contact_info' => 'canto@clases.com', 'expiration_date' => $now->copy()->addMonth()],
                ['title' => 'Clases de Bombo Legüero', 'description' => 'Aprende a tocar el bombo legüero con clases prácticas.', 'price' => '3000 por mes', 'location' => 'CABA', 'contact_info' => 'bombo@clases.com', 'expiration_date' => $now->copy()->addMonth()],
                ['title' => 'Clases de Violín Tradicional', 'description' => 'Clases de violín enfocadas en la música tradicional.', 'price' => '3200 por mes', 'location' => 'Mendoza', 'contact_info' => 'violin@clases.com', 'expiration_date' => $now->copy()->addMonth()],
            ],
            'Otros' => [
                ['title' => 'Busco Intercambio Cultural', 'description' => 'Intercambio cultural con músicos de otras regiones.', 'price' => '0', 'location' => 'Buenos Aires', 'contact_info' => 'intercambio@cultural.com', 'expiration_date' => $now->copy()->addMonth()],
                ['title' => 'Festival de Música Popular', 'description' => 'Festival de música popular con variedad de géneros.', 'price' => '0', 'location' => 'CABA', 'contact_info' => 'festival@musica.com', 'expiration_date' => $now->copy()->addMonth()],
                ['title' => 'Reunión de Amantes de la Música', 'description' => 'Reunión para compartir experiencias y conocimientos musicales.', 'price' => '0', 'location' => 'Rosario', 'contact_info' => 'reunion@musica.com', 'expiration_date' => $now->copy()->addMonth()],
            ],
        ];

        foreach ($categories as $categoryName => $classifieds) {
            $categoryId = DB::table('categories')->where('name', $categoryName)->first()->id;

            foreach ($classifieds as $classified) {
                DB::table('classifieds')->insert(array_merge($classified, ['category_id' => $categoryId]));
            }
        }
    }
}
