<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\Locality;
use App\Models\PeniaProfile;
use App\Models\Provincia;
use App\Models\User;
use Illuminate\Database\Seeder;

class PeniaProfileDemoSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::query()->firstOrFail();
        $venues = [
            ['title' => 'Peña Demo La Huella', 'province' => 'Demo Córdoba', 'city' => 'Villa del Monte', 'type' => 'penia'],
            ['title' => 'Casa Demo del Bombero', 'province' => 'Demo Salta', 'city' => 'Campo del Norte', 'type' => 'centro_cultural'],
            ['title' => 'Patio Demo del Litoral', 'province' => 'Demo Corrientes', 'city' => 'Puerto Claro', 'type' => 'gastronomico_cultural'],
            ['title' => 'Peña Demo La Arriera', 'province' => 'Demo Jujuy', 'city' => 'Quebrada Nueva', 'type' => 'penia'],
            ['title' => 'Galpón Demo del Sur', 'province' => 'Demo Río Negro', 'city' => 'Estepa Azul', 'type' => 'centro_cultural'],
            ['title' => 'Peña Demo Los Cardones', 'province' => 'Demo Tucumán', 'city' => 'Valle Chico', 'type' => 'penia'],
            ['title' => 'Fogón Demo Cuyano', 'province' => 'Demo Mendoza', 'city' => 'Viña Serena', 'type' => 'gastronomico_cultural'],
            ['title' => 'Rincón Demo Chamamecero', 'province' => 'Demo Misiones', 'city' => 'Monte Rojo', 'type' => 'penia'],
            ['title' => 'Centro Demo La Zamba', 'province' => 'Demo Santiago del Estero', 'city' => 'Solar Viejo', 'type' => 'centro_cultural'],
            ['title' => 'Peña Demo del Plata', 'province' => 'Demo Buenos Aires', 'city' => 'Ribera Sur', 'type' => 'penia'],
        ];

        foreach ($venues as $index => $venue) {
            $number = $index + 1;
            $province = Provincia::firstOrCreate(['nombre' => $venue['province']]);
            $locality = Locality::firstOrCreate(['province_id' => $province->id, 'name' => $venue['city']]);
            $slug = "penia-demo-{$number}";

            $profile = PeniaProfile::updateOrCreate(
                ['slug' => $slug],
                [
                    'title' => $venue['title'],
                    'excerpt' => 'Espacio ficticio de desarrollo para probar el directorio evergreen de Peñas.',
                    'body' => '<p>Ficha de demostración creada exclusivamente para el entorno de desarrollo. Incluye datos operativos, contacto y agenda ficticios para validar el recorrido editorial completo.</p><p>No representa un establecimiento real ni debe utilizarse fuera de DEV.</p>',
                    'province_id' => $province->id,
                    'locality_id' => $locality->id,
                    'city' => $venue['city'],
                    'address' => "Calle Demo {$number}00",
                    'latitude' => -34.6037 + ($index * 0.11),
                    'longitude' => -58.3816 + ($index * 0.09),
                    'venue_type' => $venue['type'],
                    'phone' => "+54 9 11 5555-10{$number}",
                    'email' => "penia-demo-{$number}@example.test",
                    'website' => "https://example.test/penias/{$number}",
                    'reservation_url' => "https://example.test/penias/{$number}/reservas",
                    'capacity' => 80 + ($number * 15),
                    'accessibility_notes' => 'Acceso ficticio sin escalones y baño adaptado para pruebas de interfaz.',
                    'regular_events_summary' => 'Encuentro de música y danza folklórica los viernes a las 21 h.',
                    'admission_notes' => 'Reserva ficticia sugerida; ingreso sujeto a capacidad del escenario de desarrollo.',
                    'source_urls' => ["https://example.test/fuentes/penias/{$number}"],
                    'verification_status' => 'verified',
                    'last_verified_at' => now(),
                    'verified_by_user_id' => $user->id,
                    'verification_method' => 'official_source',
                    'editorial_status' => 'published',
                    'published_at' => now()->subDay(),
                    'created_by' => $user->id,
                    'seo_title' => $venue['title'].' | Demo MFA',
                    'meta_description' => 'Ficha ficticia para validar Peñas evergreen: ubicación, contacto, agenda y verificación editorial.',
                ]
            );

            $event = Event::updateOrCreate(
                ['slug' => "evento-demo-penia-{$number}"],
                [
                    'title' => "Noche Demo en {$venue['title']}",
                    'body' => '<p>Agenda ficticia para validar la relación entre una Peña evergreen y un evento futuro.</p>',
                    'start_at' => now()->addDays($number + 7),
                    'province_id' => $province->id,
                    'city' => $venue['city'],
                    'status' => 'active',
                    'editorial_status' => 'published',
                    'published_at' => now()->subDay(),
                    'created_by' => $user->id,
                ]
            );

            $profile->events()->sync([$event->id]);
        }
    }
}
