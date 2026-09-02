<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\PeniaProfile;
use App\Models\Provincia;
use App\Models\User;
use Illuminate\Database\Seeder;

class PeniaProfileDemoSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::query()->firstOrFail();
        $province = Provincia::firstOrCreate(['nombre' => 'Provincia Demo Peñas']);

        foreach (range(1, 10) as $number) {
            $profile = PeniaProfile::updateOrCreate(
                ['slug' => "penia-demo-{$number}"],
                [
                    'title' => "Peña Demo {$number}",
                    'excerpt' => 'Ficha ficticia para validar el nuevo directorio en desarrollo.',
                    'body' => '<p>Contenido de demostración. No corresponde a un espacio cultural real.</p>',
                    'province_id' => $province->id,
                    'city' => 'Ciudad Demo',
                    'venue_type' => 'penia',
                    'source_urls' => ['https://example.test/penias-demo'],
                    'verification_status' => 'verified',
                    'last_verified_at' => now(),
                    'verified_by_user_id' => $user->id,
                    'verification_method' => 'official_source',
                    'editorial_status' => 'published',
                    'published_at' => now()->subDay(),
                    'created_by' => $user->id,
                ]
            );

            $event = Event::updateOrCreate(
                ['slug' => "evento-demo-penia-{$number}"],
                [
                    'title' => "Noche Demo en Peña {$number}",
                    'body' => '<p>Agenda de demostración para desarrollo.</p>',
                    'start_at' => now()->addDays($number + 7),
                    'province_id' => $province->id,
                    'city' => 'Ciudad Demo',
                    'status' => 'active',
                    'editorial_status' => 'published',
                    'published_at' => now()->subDay(),
                    'created_by' => $user->id,
                ]
            );

            $profile->events()->syncWithoutDetaching([$event->id]);
        }
    }
}
