<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\Locality;
use App\Models\PeniaProfile;
use App\Models\Provincia;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class PeniaProfilePilotSeeder extends Seeder
{
    public const PILOT_SLUGS = [
        'la-cautiva-salta',
        'la-panaderia-del-chuna',
        'boliche-balderrama',
        'la-casona-del-molino',
        'el-antigal-salta',
        'huayra-salta',
        'pena-del-minero-salta',
        'casa-grande-salta',
        'la-vieja-estacion-salta',
        'pena-del-chaqueno-palavecino',
    ];

    private const MUNICIPAL_DIRECTORY_URL = 'https://saltaciudad.travel/categorias/penas';

    public function run(): void
    {
        $owner = User::query()
            ->whereHas('roles', fn ($query) => $query->whereIn('name', ['admin', 'administrador']))
            ->orderBy('id')
            ->first();

        if (! $owner) {
            throw new RuntimeException('La carga piloto de Peñas requiere al menos un usuario con rol administrador.');
        }

        DB::transaction(function () use ($owner): void {
            $this->archiveKnownDemoContent();

            $province = Provincia::query()->firstOrCreate(['nombre' => 'Salta']);
            $locality = Locality::query()->firstOrCreate([
                'province_id' => $province->id,
                'name' => 'Salta',
            ]);

            foreach ($this->venues() as $venue) {
                PeniaProfile::query()->firstOrCreate(
                    ['slug' => $venue['slug']],
                    $this->profilePayload($venue, $province, $locality, $owner)
                );
            }
        });

        $this->command?->info('Lote piloto de Peñas cargado: 10 fichas en borrador y verificación pendiente.');
    }

    private function archiveKnownDemoContent(): void
    {
        PeniaProfile::query()
            ->whereIn('slug', $this->demoSlugs('penia-demo-'))
            ->each(function (PeniaProfile $profile): void {
                $profile->events()->detach();
                $profile->forceFill([
                    'editorial_status' => 'archived',
                    'published_at' => null,
                    'verification_status' => 'outdated',
                    'last_verified_at' => null,
                    'verified_by_user_id' => null,
                    'verification_method' => null,
                ])->save();
            });

        Event::query()
            ->whereIn('slug', $this->demoSlugs('evento-demo-penia-'))
            ->update([
                'editorial_status' => 'archived',
                'published_at' => null,
                'updated_at' => now(),
            ]);
    }

    private function demoSlugs(string $prefix): array
    {
        return array_map(static fn (int $number): string => $prefix.$number, range(1, 10));
    }

    private function profilePayload(array $venue, Provincia $province, Locality $locality, User $owner): array
    {
        return [
            'title' => $venue['title'],
            'excerpt' => $venue['excerpt'],
            'body' => $this->body($venue),
            'province_id' => $province->id,
            'locality_id' => $locality->id,
            'city' => 'Salta',
            'address' => $venue['address'],
            'venue_type' => 'gastronomico_cultural',
            'opening_hours' => $venue['opening_hours'] ?? null,
            'phone' => $venue['phone'],
            'email' => $venue['email'] ?? null,
            'accessibility_notes' => 'Información de accesibilidad pendiente de confirmación directa con el establecimiento.',
            'regular_events_summary' => $venue['programming'],
            'admission_notes' => 'Confirmar programación, horarios, reservas, precios y condiciones de ingreso antes de asistir.',
            'source_urls' => [$venue['source_url'], self::MUNICIPAL_DIRECTORY_URL],
            'verification_status' => 'pending',
            'last_verified_at' => null,
            'verified_by_user_id' => null,
            'verification_method' => null,
            'editorial_status' => 'draft',
            'published_at' => null,
            'created_by' => $owner->id,
            'seo_title' => $venue['title'].' en Salta | Peñas de folklore',
            'meta_description' => $venue['meta_description'],
        ];
    }

    private function body(array $venue): string
    {
        return <<<HTML
<h2>Una peña folklórica de la ciudad de Salta</h2>
<p>{$venue['overview']}</p>
<p>La ficha del organismo municipal de turismo la incluye dentro de la oferta de peñas de la ciudad. Esa fuente permite identificar el espacio y sus datos básicos, pero no reemplaza la confirmación directa de una función, una reserva o una condición de acceso.</p>
<h2>Propuesta cultural y gastronómica</h2>
<p>{$venue['experience']}</p>
<p>Como sucede con otros espacios de música en vivo, artistas, horarios y modalidades pueden variar entre fechas. Por eso esta ficha separa la identidad estable de la peña de la agenda eventual, que deberá incorporarse como eventos fechados y con su propia verificación.</p>
<h2>Información práctica antes de la visita</h2>
<p>El domicilio informado es {$venue['address']}, en la ciudad de Salta. El teléfono publicado por Turismo de la Ciudad de Salta es {$venue['phone']}. Conviene consultar con anticipación la programación vigente, la necesidad de reserva, el valor de la entrada o consumición y las condiciones de accesibilidad.</p>
<p>Esta ficha ingresó al portal como borrador editorial. Un administrador debe contrastar los datos con el establecimiento, documentar el método y la fecha de verificación, revisar derechos de imagen y recién entonces decidir su publicación.</p>
HTML;
    }

    private function venues(): array
    {
        return [
            [
                'slug' => 'la-cautiva-salta', 'title' => 'La Cautiva', 'address' => 'Balcarce 892',
                'phone' => '+54 387 414-0494', 'source_url' => 'https://saltaciudad.travel/posts/pena-la-cautiva',
                'excerpt' => 'Peña folklórica del Paseo Balcarce con propuesta gastronómica y espectáculo musical.',
                'overview' => 'La Cautiva funciona sobre el Paseo Balcarce, uno de los corredores culturales y gastronómicos más conocidos de la capital salteña. La presentación municipal destaca una atención cercana y una propuesta pensada para acercar folklore y cocina regional.',
                'experience' => 'Su perfil combina gastronomía con espectáculo folklórico. La fuente municipal también menciona su adhesión al Sello CocinAR, referencia que debe volver a comprobarse durante la revisión editorial previa a publicar.',
                'programming' => 'Espectáculos folklóricos; días, artistas y horarios sujetos a confirmación.',
                'meta_description' => 'Conocé La Cautiva, peña folklórica del Paseo Balcarce en Salta: ubicación, contacto, propuesta cultural y datos a verificar antes de asistir.',
            ],
            [
                'slug' => 'la-panaderia-del-chuna', 'title' => 'La Panadería del Chuña', 'address' => 'Balcarce 446',
                'phone' => '+54 387 431-2896', 'source_url' => 'https://saltaciudad.travel/posts/pena-la-panaderia-del-chuna',
                'excerpt' => 'Peña y espacio gastronómico ubicado en el corredor cultural de la calle Balcarce.',
                'overview' => 'La Panadería del Chuña se encuentra en la calle Balcarce de la ciudad de Salta. La ficha municipal la presenta como una peña de amplias dimensiones vinculada con el circuito de música y gastronomía regional de la zona.',
                'experience' => 'La propuesta reúne encuentro folklórico, cocina y espectáculos. Turismo municipal señala su participación en el Sello CocinAR; esa distinción y cualquier servicio vigente deben ratificarse antes de convertir el borrador en una recomendación pública.',
                'programming' => 'Encuentros y espectáculos folklóricos; consultar cartelera y reservas.',
                'meta_description' => 'Ficha de La Panadería del Chuña en Salta: dirección, teléfono, propuesta folklórica y recomendaciones para confirmar la visita.',
            ],
            [
                'slug' => 'boliche-balderrama', 'title' => 'Boliche Balderrama', 'address' => 'Avenida San Martín 1126',
                'phone' => '+54 387 410-7392', 'source_url' => 'https://saltaciudad.travel/posts/pena-boliche-balderrama',
                'excerpt' => 'Histórico espacio salteño ligado al folklore, la gastronomía regional y la vida cultural local.',
                'overview' => 'Boliche Balderrama es un nombre histórico de la cultura popular salteña. La información turística municipal sitúa su trayectoria en más de siete décadas y lo reconoce como un espacio tradicional asociado a la memoria folklórica de la ciudad.',
                'experience' => 'La propuesta articula música folklórica en vivo y platos regionales. Su relevancia excede una agenda puntual: forma parte del imaginario cultural de Salta, aunque cada espectáculo y servicio debe verificarse para la fecha concreta de la visita.',
                'programming' => 'La fuente municipal informa espectáculos habituales; confirmar programación vigente.',
                'meta_description' => 'Boliche Balderrama en Salta: historia, propuesta folklórica, dirección, teléfono y datos prácticos sujetos a verificación.',
            ],
            [
                'slug' => 'la-casona-del-molino', 'title' => 'La Casona del Molino', 'address' => 'Luis Burela 1',
                'phone' => '+54 387 576-1099', 'source_url' => 'https://saltaciudad.travel/posts/pena-la-casona-del-molino',
                'excerpt' => 'Casona tradicional de Salta conocida por sus guitarreadas espontáneas y cocina regional.',
                'overview' => 'La Casona del Molino ocupa una casa de rasgos coloniales en la ciudad de Salta. La ficha de turismo municipal la identifica como un lugar tradicional de reunión donde la experiencia se organiza alrededor de mesas, patios, música compartida y gastronomía regional.',
                'experience' => 'Su rasgo distintivo son las guitarreadas espontáneas: la música puede surgir de quienes visitan el espacio y no únicamente de una cartelera formal. La fuente también registra su adhesión al Sello CocinAR, dato que requiere comprobación periódica.',
                'programming' => 'Guitarreadas y música compartida; modalidad y disponibilidad sujetas a confirmación.',
                'meta_description' => 'La Casona del Molino en Salta: guitarreadas, cocina regional, ubicación, teléfono y claves para planificar la visita.',
            ],
            [
                'slug' => 'el-antigal-salta', 'title' => 'El Antigal', 'address' => 'Balcarce 876',
                'phone' => '+54 387 575-8514', 'email' => 'elantigalsalta@gmail.com',
                'source_url' => 'https://saltaciudad.travel/posts/pena-el-antigal',
                'excerpt' => 'Peña de la calle Balcarce con cena, espectáculo folklórico y gastronomía regional.',
                'overview' => 'El Antigal está ubicado en el corredor de la calle Balcarce. Turismo municipal lo presenta como una peña orientada a la cena con espectáculo, dentro de una zona donde conviven propuestas gastronómicas, musicales y culturales.',
                'experience' => 'La descripción oficial destaca la participación artística durante el servicio, con música y danza vinculadas al folklore, junto con platos regionales. El formato exacto de cada noche puede cambiar y debe confirmarse directamente.',
                'programming' => 'Cena espectáculo con música y danza; consultar días, elenco y reservas.',
                'meta_description' => 'El Antigal en Salta: cena espectáculo folklórica, dirección, teléfono, contacto y datos que conviene confirmar antes de reservar.',
            ],
            [
                'slug' => 'huayra-salta', 'title' => 'Huayra', 'address' => 'Balcarce 817',
                'phone' => '+54 387 595-2948', 'source_url' => 'https://saltaciudad.travel/posts/pena-huayra',
                'excerpt' => 'Peña de Salta con espectáculos folklóricos y una propuesta centrada en sabores regionales.',
                'overview' => 'Huayra integra el conjunto de peñas del Paseo Balcarce relevadas por Turismo de la Ciudad de Salta. Su identidad pública combina una experiencia musical folklórica con una oferta gastronómica ligada a la región.',
                'experience' => 'La ficha municipal menciona espectáculos y comidas regionales, además de su inclusión en el Sello CocinAR. La vigencia de la distinción, la cartelera y la modalidad de atención forman parte de la verificación administrativa pendiente.',
                'programming' => 'Espectáculos folklóricos; confirmar programación, horarios y modalidad de reserva.',
                'meta_description' => 'Huayra en Salta: peña, folklore y gastronomía regional en Balcarce, con contacto y recomendaciones de verificación.',
            ],
            [
                'slug' => 'pena-del-minero-salta', 'title' => 'Peña del Minero', 'address' => 'Olavarría 934',
                'phone' => '+54 387 449-4747', 'source_url' => 'https://saltaciudad.travel/posts/pena-del-minero',
                'excerpt' => 'Espacio folklórico salteño cuya identidad recupera referencias a la cultura y memoria minera.',
                'overview' => 'Peña del Minero se encuentra fuera del eje inmediato de la calle Balcarce y construye su identidad alrededor de referencias a la actividad minera. La guía municipal la incorpora a la oferta cultural y gastronómica de peñas de la capital.',
                'experience' => 'El espacio vincula música folklórica, gastronomía regional y una ambientación asociada con la memoria minera. La ficha no debe interpretarse como una agenda: actuaciones, menú y condiciones de acceso requieren consulta actualizada.',
                'programming' => 'Música folklórica y propuesta gastronómica; consultar agenda y reservas.',
                'meta_description' => 'Peña del Minero en Salta: identidad cultural, folklore, gastronomía, ubicación y contacto para confirmar la visita.',
            ],
            [
                'slug' => 'casa-grande-salta', 'title' => 'Casa Grande', 'address' => 'Alberdi 1107',
                'phone' => '+54 387 636-1920', 'source_url' => 'https://saltaciudad.travel/posts/pena-casa-grande',
                'excerpt' => 'Peña familiar de Salta con música en vivo y guitarreada, alejada del circuito más comercial.',
                'overview' => 'Casa Grande es presentada por Turismo municipal como una peña joven y familiar situada fuera del circuito comercial más transitado. La experiencia se desarrolla en una casa adaptada como lugar de encuentro cultural.',
                'experience' => 'La propuesta combina un espectáculo de música en vivo con un momento posterior de guitarreada. Ese formato favorece la participación y la cercanía, pero debe comprobarse junto con los días y horarios antes de publicarlo como programación vigente.',
                'programming' => 'Música en vivo seguida de guitarreada; confirmar días y horarios.',
                'opening_hours' => ['miércoles a sábado' => '20:30–02:00 (según fuente municipal; confirmar)'],
                'meta_description' => 'Casa Grande en Salta: peña familiar con música y guitarreada, dirección, teléfono y horarios sujetos a confirmación.',
            ],
            [
                'slug' => 'la-vieja-estacion-salta', 'title' => 'La Vieja Estación', 'address' => 'Balcarce 875',
                'phone' => '+54 387 523-4888', 'source_url' => 'https://saltaciudad.travel/posts/pena-la-vieja-estacion',
                'excerpt' => 'Peña del Paseo Balcarce vinculada desde sus orígenes con el Centro Cultural Jorge Cafrune.',
                'overview' => 'La Vieja Estación ocupa un lugar reconocido en el desarrollo del circuito de peñas del Paseo Balcarce. La reseña municipal vincula sus comienzos con el Centro Cultural Jorge Cafrune y con la consolidación cultural de la zona.',
                'experience' => 'La propuesta reúne música folklórica y gastronomía regional, con alternativas que la fuente también describe como gourmet. La adhesión informada al Sello CocinAR y toda oferta actual deberán ratificarse durante la verificación editorial.',
                'programming' => 'Espectáculos de música folklórica; consultar cartelera y condiciones de ingreso.',
                'meta_description' => 'La Vieja Estación en Salta: historia en el Paseo Balcarce, folklore, gastronomía, dirección y teléfono.',
            ],
            [
                'slug' => 'pena-del-chaqueno-palavecino', 'title' => 'Peña del Chaqueño Palavecino', 'address' => 'Balcarce 935',
                'phone' => '+54 387 525-9480', 'source_url' => 'https://saltaciudad.travel/posts/pena-del-chaqueno-palavecino',
                'excerpt' => 'Peña de la ciudad de Salta asociada al Chaqueño Palavecino, con shows y cocina regional.',
                'overview' => 'La Peña del Chaqueño Palavecino está ubicada en la calle Balcarce y toma su identidad del reconocido cantor folklórico salteño. La oficina municipal de turismo la lista entre los espacios culturales y gastronómicos de la ciudad.',
                'experience' => 'Su propuesta anunciada combina espectáculos folklóricos con cocina regional. La relación nominal con el artista no implica su presencia en cada fecha; el elenco, la programación y cualquier actuación especial deben confirmarse por canales oficiales.',
                'programming' => 'Shows folklóricos; consultar artistas, horarios y reservas para cada fecha.',
                'opening_hours' => ['lunes a domingo' => '20:30–02:00 (según fuente municipal; confirmar)'],
                'meta_description' => 'Peña del Chaqueño Palavecino en Salta: folklore, gastronomía, ubicación, teléfono y programación a confirmar.',
            ],
        ];
    }
}
