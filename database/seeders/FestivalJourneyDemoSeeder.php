<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\Festival;
use App\Models\Interprete;
use App\Models\Mes;
use App\Models\Provincia;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class FestivalJourneyDemoSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::firstOrCreate(
            ['email' => 'festival-vivo-demo@mifolkloreargentino.com'],
            [
                'name' => 'Festival Vivo Demo',
                'password' => Hash::make(Str::random(40)),
            ]
        );

        $province = Provincia::firstOrCreate(['nombre' => 'Provincia Demo Festival Vivo']);
        $month = Mes::firstOrCreate(['nombre' => 'Septiembre']);

        foreach (['Encuentro Demo del Litoral', 'Fiesta Demo de la Zamba'] as $index => $title) {
            $festival = Festival::updateOrCreate(
                ['slug' => str($title)->slug()->toString()],
                [
                    'title' => $title,
                    'excerpt' => 'Festival de demostracion para el recorrido transversal.',
                    'body' => '<p>Contenido estable verificable para pruebas locales.</p>',
                    'province_id' => $province->id,
                    'mes_id' => $month->id,
                    'seo_title' => $title,
                    'meta_description' => 'Escenario de desarrollo para Festival vivo.',
                    'status' => 'published',
                    'published_at' => now()->subDay(),
                    'user_id' => $user->id,
                ]
            );

            $artist = Interprete::updateOrCreate(
                ['slug' => 'artista-demo-festival-vivo-'.($index + 1)],
                ['interprete' => 'Artista Demo Festival Vivo '.($index + 1), 'biografia' => str_repeat('Biografia de demostracion. ', 12), 'estado' => 1, 'user_id' => $user->id]
            );

            $event = Event::updateOrCreate(
                ['slug' => 'evento-demo-festival-vivo-'.($index + 1)],
                [
                    'title' => 'Proxima fecha de '.$title,
                    'body' => '<p>Evento futuro de demostracion.</p>',
                    'start_at' => now()->addDays(14 + $index),
                    'province_id' => $province->id,
                    'city' => 'Ciudad Demo',
                    'status' => 'active',
                    'editorial_status' => 'published',
                    'published_at' => now()->subDay(),
                    'created_by' => $user->id,
                ]
            );

            $festival->events()->syncWithoutDetaching([$event->id]);
            $festival->interpretes()->syncWithoutDetaching([$artist->id]);
            $event->interpretes()->syncWithoutDetaching([$artist->id => ['sort_order' => 1]]);
        }
    }
}
