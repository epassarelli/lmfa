<?php

// Carga local autorizada por el usuario; nunca ejecutar en producción.
require __DIR__.'/../../../vendor/autoload.php';
$app = require __DIR__.'/../../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Event;
use App\Models\EventProgram;
use App\Models\Interprete;
use App\Models\Provincia;
use App\Models\User;
use App\Models\Venue;
use App\Services\EventProgramService;
use App\Services\EventService;
use App\Services\VenueService;
use Illuminate\Support\Facades\DB;

if (! app()->environment('local') || config('database.default') !== 'mysql' || config('database.connections.mysql.database') !== 'mfa') {
    throw new RuntimeException('Carga restringida a local/mysql/mfa.');
}
$titles = ['[DEMO LOCAL] Encuentro del Río — 4 jornadas', '[DEMO LOCAL] Fiesta de la Luna — 5 jornadas'];
$singleTitle = '[DEMO LOCAL] Peña del Patio — evento independiente';
$notice = '<p><strong>DEMO LOCAL: evento, fechas, precios, sedes y vínculos con intérpretes ficticios. No es una programación real ni una participación confirmada.</strong></p>';
$programs = EventProgram::whereIn('title', $titles)->get();
$single = Event::where('title', $singleTitle)->get();
$complete = $programs->count() === 2 && $single->count() === 1;
if ($complete) {
    foreach ($programs as $program) {
        $expected = $program->title === $titles[0] ? 4 : 5;
        if ($program->events()->count() !== $expected || $program->events()->whereDoesntHave('interpretes')->exists()) {
            throw new RuntimeException('Demo modificada/incompleta: no se sobrescribe.');
        }
    }
} elseif ($programs->isNotEmpty() || $single->isNotEmpty() || Event::where('title', 'like', '[DEMO LOCAL] Encuentro del Río%')->exists() || Event::where('title', 'like', '[DEMO LOCAL] Fiesta de la Luna%')->exists() || Venue::whereIn('name', ['[DEMO LOCAL] Anfiteatro del Río', '[DEMO LOCAL] Sala de la Luna'])->exists()) {
    throw new RuntimeException('Carga parcial: detener sin modificar ni duplicar.');
}
if (! $complete && ! in_array('--apply', $argv, true)) {
    echo "Plan: insertar 2 conjuntos (4 y 5 jornadas), 1 evento independiente, 2 sedes ficticias. Reutilizar intérpretes activos sin modificar perfiles. Publicados solo en local. Usar --apply para aplicar.\n";
    exit;
}
if (! $complete) {
    $admin = User::role('administrador')->orderBy('id')->firstOrFail();
    $artists = Interprete::where('estado', 1)->orderBy('id')->limit(6)->pluck('id')->all();
    if (count($artists) !== 6) {
        throw new RuntimeException('Se necesitan seis intérpretes activos existentes.');
    }
    $province = Provincia::where('nombre', 'like', 'C%rdoba')->first() ?? Provincia::orderBy('id')->firstOrFail();
    auth()->login($admin);
    DB::transaction(function () use ($titles, $singleTitle, $notice, $artists, $province) {
        $venues = [];
        foreach (['Anfiteatro del Río', 'Sala de la Luna'] as $i => $name) {
            $venues[] = app(VenueService::class)->save([
                'name' => '[DEMO LOCAL] '.$name, 'city' => 'Localidad ficticia '.($i + 1),
                'address' => 'Calle de prueba '.(100 + $i), 'province_id' => $province->id,
                'description' => 'Sede ficticia exclusiva para la demostración local.', 'status' => 'active',
            ]);
        }
        foreach ([4, 5] as $index => $days) {
            $base = now()->addDays($index === 0 ? 7 : 21)->startOfDay();
            $sessions = [];
            for ($day = 0; $day < $days; $day++) {
                $start = $base->copy()->addDays($day)->setTime(21, 0);
                $sessions[] = array_merge([
                    'title' => '[DEMO LOCAL] '.($index === 0 ? 'Encuentro del Río' : 'Fiesta de la Luna').' — jornada '.($day + 1),
                    'start_at' => $start->toDateTimeString(), 'end_at' => $start->copy()->addHours(5)->toDateTimeString(),
                    'interprete_ids' => [$artists[$day % 6], $artists[($day + 2 + $index) % 6]],
                ], $day === 1 ? ['price_text' => '$ 8.000 ficticios (excepción de esta jornada)'] : []);
            }
            app(EventProgramService::class)->save([
                'title' => $titles[$index], 'body' => $notice.'<h2>Detalle común</h2><p>Este texto y la sede se heredan en las jornadas. La jornada 2 tiene un precio propio; cada noche tiene artistas y horarios propios.</p>',
                'excerpt' => 'Programación ficticia local para probar conjuntos y jornadas.',
                'venue_id' => $venues[$index]->id, 'price_text' => '$ 5.000 ficticios por jornada', 'is_free' => false,
                'editorial_status' => 'published', 'sessions' => $sessions,
            ]);
        }
        $start = now()->addDays(35)->setTime(20, 0);
        app(EventService::class)->createEvent([
            'title' => $singleTitle, 'body' => $notice.'<h2>Evento independiente</h2><p>Una sola fecha, sin conjunto. Reutiliza el Anfiteatro del Río del catálogo de lugares.</p>',
            'start_at' => $start->toDateTimeString(), 'end_at' => $start->copy()->addHours(4)->toDateTimeString(),
            'venue_id' => $venues[0]->id, 'is_free' => true, 'editorial_status' => 'published',
            'interprete_ids' => [$artists[4], $artists[5]],
        ]);
    });
    auth()->logout();
}
$programs = EventProgram::whereIn('title', $titles)->with(['events.interpretes', 'venue'])->orderBy('id')->get();
$single = Event::where('title', $singleTitle)->with(['interpretes', 'venue'])->firstOrFail();
$eventInfo = fn ($event) => [
    'id' => $event->id, 'title' => $event->title, 'start_at' => $event->start_at->toDateTimeString(),
    'venue_id' => $event->venue_id, 'program_id' => $event->program_id, 'overrides' => $event->program_overrides,
    'interpretes' => $event->interpretes->map(fn ($artist) => ['id' => $artist->id, 'name' => $artist->interprete])->all(),
    'public_path' => '/cartelera-de-eventos-folkloricos/'.$event->slug, 'edit_path' => '/admin/events/'.$event->id.'/edit',
];
$manifest = ['created_now' => ! $complete, 'programs' => $programs->map(fn ($program) => [
    'id' => $program->id, 'title' => $program->title, 'venue_id' => $program->venue_id,
    'public_path' => '/programacion-de-eventos/'.$program->slug, 'edit_path' => '/admin/event-programs/'.$program->id.'/edit',
    'sessions' => $program->events->map($eventInfo)->all(),
])->all(), 'single' => $eventInfo($single)];
$folder = storage_path('app/local-demos');
if (! is_dir($folder)) {
    mkdir($folder, 0775, true);
}
file_put_contents($folder.'/event-programs-manifest.json', json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
echo json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)."\n";
