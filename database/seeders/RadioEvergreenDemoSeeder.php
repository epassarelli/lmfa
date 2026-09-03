<?php

namespace Database\Seeders;

use App\Models\Provincia;
use App\Models\RadioProgram;
use App\Models\RadioSignal;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class RadioEvergreenDemoSeeder extends Seeder
{
    public function run(): void
    {
        $editor = User::query()->firstOrCreate(['email' => 'editor.radios.demo@example.test'], ['name' => 'Editor Radios Demo', 'password' => bcrypt(Str::random(32))]);
        $provinces = Provincia::query()->orderBy('id')->get()->keyBy('id');
        $signals = [
            ['title' => 'Radio Cosquín FM', 'city' => 'Cosquín', 'mode' => ['air', 'streaming'], 'focus' => 'folklore', 'channel' => ['label' => 'FM 95.1', 'channel_type' => 'frequency', 'frequency_band' => 'FM', 'frequency' => '95.1']],
            ['title' => 'Raíces del Litoral', 'city' => 'Posadas', 'mode' => ['streaming'], 'focus' => 'folklore', 'channel' => ['label' => 'Escuchar en vivo', 'channel_type' => 'stream', 'platform' => 'stream_directo', 'url' => 'https://example.test/raices-litoral']],
            ['title' => 'La Salamanca Radio', 'city' => 'Santiago del Estero', 'mode' => ['air'], 'focus' => 'folklore', 'channel' => ['label' => 'AM 780', 'channel_type' => 'frequency', 'frequency_band' => 'AM', 'frequency' => '780']],
            ['title' => 'Radio Nacional Folklórica Demo', 'city' => 'Buenos Aires', 'mode' => ['air', 'web'], 'focus' => 'mixed', 'channel' => ['label' => 'Sitio oficial', 'channel_type' => 'website', 'platform' => 'sitio_web', 'url' => 'https://example.test/nacional-folklorica']],
            ['title' => 'Cuyo Sonoro', 'city' => 'Mendoza', 'mode' => ['streaming'], 'focus' => 'folklore', 'channel' => ['label' => 'Canal YouTube', 'channel_type' => 'platform', 'platform' => 'youtube', 'url' => 'https://youtube.com/@cuyosonoro']],
        ];

        foreach ($signals as $index => $data) {
            $signal = RadioSignal::updateOrCreate(['slug' => Str::slug($data['title'])], [
                'title' => $data['title'], 'excerpt' => 'Señal demo con programación y música de raíz folklórica.', 'body' => '<p>Señal demo para revisar el directorio evergreen de Radios.</p>', 'editorial_focus' => $data['focus'], 'transmission_modes' => $data['mode'], 'province_id' => $provinces->keys()->get($index % max($provinces->count(), 1)), 'city' => $data['city'], 'coverage_scope' => 'regional', 'source_urls' => ['https://example.test/fuente-radio'], 'verification_status' => 'verified', 'last_verified_at' => now(), 'verified_by_user_id' => $editor->id, 'verification_method' => 'manual', 'editorial_status' => 'published', 'published_at' => now(), 'created_by' => $editor->id,
            ]);
            $signal->listeningChannels()->updateOrCreate(['label' => $data['channel']['label']], $data['channel'] + ['is_active' => true, 'is_primary' => true, 'sort_order' => 0]);
            $program = RadioProgram::updateOrCreate(['slug' => Str::slug('Peña al aire '.$data['title'])], ['radio_signal_id' => $signal->id, 'title' => 'Peña al aire - '.$data['title'], 'excerpt' => 'Programa semanal de folklore.', 'body' => '<p>Programa demo emitido por una señal del directorio.</p>', 'is_folklore' => true, 'source_urls' => ['https://example.test/programa-radio'], 'verification_status' => 'verified', 'last_verified_at' => now(), 'verified_by_user_id' => $editor->id, 'verification_method' => 'manual', 'editorial_status' => 'published', 'published_at' => now(), 'created_by' => $editor->id]);
            $program->slots()->updateOrCreate(['weekday' => $index + 1, 'starts_at' => '20:00:00'], ['ends_at' => '22:00:00', 'timezone' => 'America/Argentina/Buenos_Aires', 'is_active' => true]);
        }

        foreach (['Folklore en vivo', 'Guitarras del país', 'Voces de la tierra', 'Canto y memoria', 'Nuevo cancionero'] as $index => $title) {
            $program = RadioProgram::updateOrCreate(['slug' => Str::slug($title)], ['title' => $title, 'excerpt' => 'Stream independiente dedicado al folklore argentino.', 'body' => '<p>Programa demo independiente para explorar la grilla.</p>', 'is_folklore' => true, 'platform' => $index % 2 ? 'facebook' : 'youtube', 'listening_url' => 'https://example.test/stream-'.($index + 1), 'source_urls' => ['https://example.test/programa-independiente'], 'verification_status' => 'verified', 'last_verified_at' => now(), 'verified_by_user_id' => $editor->id, 'verification_method' => 'manual', 'editorial_status' => 'published', 'published_at' => now(), 'created_by' => $editor->id]);
            $program->slots()->updateOrCreate(['weekday' => $index, 'starts_at' => '18:00:00'], ['ends_at' => '19:00:00', 'timezone' => 'America/Argentina/Buenos_Aires', 'is_active' => true]);
        }
    }
}
