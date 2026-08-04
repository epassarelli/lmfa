<?php

namespace Database\Seeders;

use App\Models\KnowledgeCategory;
use Illuminate\Database\Seeder;

class KnowledgeCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Ritmos',
                'slug' => 'ritmos',
                'sort_order' => 10,
                'description' => 'Explicaciones claras sobre chacarera, zamba, chamame, gato y otros ritmos fundamentales del folklore argentino.',
                'seo_title' => 'Ritmos del folklore argentino: chacarera, zamba, chamame y mas',
                'meta_description' => 'Descubri los ritmos del folklore argentino con origen, caracteristicas, diferencias y ejemplos clave de chacarera, zamba, chamame y mas.',
            ],
            [
                'name' => 'Danzas',
                'slug' => 'danzas',
                'sort_order' => 20,
                'description' => 'Guias para entender y bailar las danzas folkloricas argentinas, desde sus pasos basicos hasta su contexto cultural.',
                'seo_title' => 'Danzas folkloricas argentinas: pasos, estilos y significado',
                'meta_description' => 'Aprende sobre las danzas folkloricas argentinas con guias de pasos, coreografias tradicionales, contexto historico y claves para bailarlas mejor.',
            ],
            [
                'name' => 'Instrumentos',
                'slug' => 'instrumentos',
                'sort_order' => 30,
                'description' => 'Fichas de bombo leguero, guitarra, violin, charango y otros instrumentos esenciales del sonido folklorico argentino.',
                'seo_title' => 'Instrumentos del folklore argentino: bombo, guitarra, violin y mas',
                'meta_description' => 'Conoce los instrumentos del folklore argentino, como se usan, que sonido aportan y por que son centrales en peñas, festivales y grabaciones.',
            ],
            [
                'name' => 'Regiones',
                'slug' => 'regiones',
                'sort_order' => 40,
                'description' => 'Una mirada regional para entender como cambia el folklore argentino segun geografia, historia y tradiciones locales.',
                'seo_title' => 'Regiones del folklore argentino: NOA, Litoral, Cuyo, Centro y Patagonia',
                'meta_description' => 'Explora las regiones del folklore argentino y sus rasgos musicales, culturales y tradicionales en el NOA, Litoral, Cuyo, Centro y Patagonia.',
            ],
            [
                'name' => 'Provincias',
                'slug' => 'provincias',
                'sort_order' => 50,
                'description' => 'Recorridos por cada provincia para descubrir artistas, ritmos, fiestas y simbolos propios del folklore argentino.',
                'seo_title' => 'Folklore argentino por provincias: artistas, ritmos y tradiciones',
                'meta_description' => 'Consulta el folklore argentino por provincias con contenidos sobre referentes, fiestas populares, ritmos locales y tradiciones de cada territorio.',
            ],
            [
                'name' => 'Historia',
                'slug' => 'historia',
                'sort_order' => 60,
                'description' => 'Articulos de contexto para comprender los procesos, figuras y momentos que marcaron la historia del folklore argentino.',
                'seo_title' => 'Historia del folklore argentino: origen, expansion y figuras clave',
                'meta_description' => 'Lee articulos sobre la historia del folklore argentino, desde sus origenes hasta su expansion en radios, festivales, discos y escenarios del pais.',
            ],
            [
                'name' => 'Tradiciones',
                'slug' => 'tradiciones',
                'sort_order' => 70,
                'description' => 'Costumbres, celebraciones, peñas y expresiones populares que mantienen vivo el folklore argentino en todo el pais.',
                'seo_title' => 'Tradiciones del folklore argentino: peñas, fiestas y costumbres',
                'meta_description' => 'Descubre las tradiciones del folklore argentino a traves de peñas, fiestas populares, rituales, costumbres y practicas culturales vivas.',
            ],
            [
                'name' => 'Cancionero',
                'slug' => 'cancionero',
                'sort_order' => 80,
                'description' => 'Historias, contexto y significado de canciones fundamentales para entender el repertorio del folklore argentino.',
                'seo_title' => 'Cancionero folklorico argentino: historias y significado de canciones',
                'meta_description' => 'Explora el cancionero folklorico argentino con articulos sobre letras, contexto, origen e impacto cultural de obras emblematicas.',
            ],
            [
                'name' => 'Aprender',
                'slug' => 'aprender',
                'sort_order' => 90,
                'description' => 'Contenidos practicos para quienes quieren tocar, cantar, escuchar o iniciarse en el folklore argentino con mejores bases.',
                'seo_title' => 'Aprender folklore argentino: guias para tocar, cantar y escuchar mejor',
                'meta_description' => 'Encuentra guias para aprender folklore argentino con recursos practicos para guitarra, canto, escucha, acompanamiento y formacion inicial.',
            ],
        ];

        foreach ($categories as $category) {
            $record = KnowledgeCategory::firstOrNew(['slug' => $category['slug']]);
            $record->fill(array_merge($category, ['is_active' => true]));
            $record->save();
        }
    }
}
