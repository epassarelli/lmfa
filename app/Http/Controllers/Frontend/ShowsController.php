<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Interprete;
use App\Models\Provincia;
use App\Support\SeoMetadata;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class ShowsController extends Controller
{
    private const MONTHS = [
        1 => 'enero',
        2 => 'febrero',
        3 => 'marzo',
        4 => 'abril',
        5 => 'mayo',
        6 => 'junio',
        7 => 'julio',
        8 => 'agosto',
        9 => 'septiembre',
        10 => 'octubre',
        11 => 'noviembre',
        12 => 'diciembre',
    ];

    public function resolve(Request $request, string $provinceOrSlug, ?string $period = null)
    {
        $provincia = $this->findProvinciaBySlug($provinceOrSlug);

        if ($provincia) {
            $request->merge([
                'province_slug' => $provincia->slug,
                'province_id' => $provincia->id,
            ]);

            if ($period === 'hoy') {
                $request->merge(['today' => '1']);
            } elseif ($period) {
                $request->merge(['mes' => $period]);
            }

            return $this->index($request);
        }

        if ($period !== null) {
            abort(404);
        }

        return $this->show($provinceOrSlug);
    }

    public function index(Request $request)
    {
        $filters = $this->resolveFilters($request);

        $query = Event::query()
            ->where('editorial_status', 'published')
            ->where('start_at', '>=', now()->startOfDay());

        if ($filters['provincia']) {
            $query->where('province_id', $filters['provincia']->id);
        }

        if ($filters['month_start'] && $filters['month_end']) {
            $query->whereBetween('start_at', [$filters['month_start'], $filters['month_end']]);
        }

        if ($filters['is_today']) {
            $query->whereDate('start_at', today());
        }

        if ($filters['specific_date']) {
            $query->whereDate('start_at', $filters['specific_date']->toDateString());
        }

        if ($filters['interprete']) {
            $query->whereHas('interpretes', function ($subQuery) use ($filters) {
                $subQuery->where('interpretes.id', $filters['interprete']->id);
            });
        }

        if ($filters['search']) {
            $search = $filters['search'];
            $query->where(function ($subQuery) use ($search) {
                $subQuery->where('title', 'like', "%{$search}%")
                    ->orWhere('excerpt', 'like', "%{$search}%")
                    ->orWhere('body', 'like', "%{$search}%")
                    ->orWhere('city', 'like', "%{$search}%")
                    ->orWhere('address', 'like', "%{$search}%")
                    ->orWhereHas('interpretes', function ($artistQuery) use ($search) {
                        $artistQuery->where('interprete', 'like', "%{$search}%");
                    });
            });
        }

        $shows = $query
            ->with(['interpretes.images', 'images', 'provincia'])
            ->orderBy('start_at')
            ->paginate(12)
            ->withQueryString();

        $interpretes = Cache::remember('shows:index:interpretes', now()->addHours(1), fn () => Interprete::active()->get());
        $provincias = Cache::remember('shows:index:provincias', now()->addHours(1), fn () => Provincia::orderBy('nombre')->get());
        $sinResultados = $shows->count() === 0;

        [$heading, $metaTitle, $metaDescription, $introText] = $this->buildSeoContent($filters);
        [$canonicalUrl, $metaRobots] = $this->buildCanonicalAndRobots($filters);
        $breadcrumbs = $this->buildBreadcrumbs($filters);
        $relatedProvinceLinks = Cache::remember('shows:index:related-provinces', now()->addHours(1), fn () => Provincia::orderBy('nombre')->take(8)->get());

        return view('frontend.shows.index', [
            'shows' => $shows,
            'interpretes' => $interpretes,
            'provincias' => $provincias,
            'metaTitle' => $metaTitle,
            'metaDescription' => $metaDescription,
            'metaRobots' => $metaRobots,
            'canonicalUrl' => $canonicalUrl,
            'sinResultados' => $sinResultados,
            'breadcrumbs' => $breadcrumbs,
            'heading' => $heading,
            'introText' => $introText,
            'filters' => $filters,
            'relatedProvinceLinks' => $relatedProvinceLinks,
            'monthOptions' => $this->buildMonthOptions(),
        ]);
    }

    public function byArtista($slug)
    {
        $interprete = Interprete::where('slug', $slug)->firstOrFail();
        $shows = $interprete->events()->with(['images', 'interpretes.images'])->get();
        $interpretes = Interprete::getInterpretesExcluding($interprete->id);

        $section = 'shows';

        $metaTitle = 'Shows de '.$interprete->interprete;
        $metaDescription = 'Cartelera de shows de '.$interprete->interprete.', interprete del folklore argentino';

        $breadcrumbs = [
            ['label' => 'Artistas', 'url' => route('interpretes.index')],
            ['label' => $interprete->interprete, 'url' => route('artista.show', $interprete->slug)],
            ['label' => 'Shows'],
        ];

        return view('frontend.shows.byArtista', compact('shows', 'interprete', 'interpretes', 'section', 'metaTitle', 'metaDescription', 'breadcrumbs'));
    }

    public function show($slug)
    {
        $show = Event::with(['interpretes.images', 'provincia', 'images'])->where('slug', $slug)->firstOrFail();

        $ultimos_shows = Event::where('editorial_status', 'published')
            ->where('id', '<>', $show->id)
            ->orderByDesc('created_at')
            ->take(10)
            ->get();

        $noticiasRelacionadas = $show->noticias ?? collect();

        $seo = SeoMetadata::event($show);
        $metaTitle = $seo['title'];
        $metaDescription = $seo['description'];
        $h1 = $seo['h1'];

        $breadcrumbs = [
            ['label' => 'Cartelera', 'url' => route('cartelera.index')],
            ['label' => $show->titulo],
        ];

        return view('frontend.shows.show', compact(
            'show',
            'ultimos_shows',
            'metaTitle',
            'metaDescription',
            'h1',
            'noticiasRelacionadas',
            'breadcrumbs'
        ));
    }

    private function resolveFilters(Request $request): array
    {
        $provincia = null;
        $provinceId = $request->input('province_id', $request->input('provincia_id'));
        $provinceSlug = $request->input('province_slug');

        if ($provinceSlug) {
            $provincia = $this->findProvinciaBySlug($provinceSlug);
        }

        if (! $provincia && $provinceId) {
            $provincia = Provincia::find($provinceId);
        }

        $interprete = null;
        if ($request->filled('interprete_id')) {
            $interprete = Interprete::find($request->input('interprete_id'));
        } elseif ($request->filled('interprete')) {
            $interprete = Interprete::where('interprete', $request->input('interprete'))->first();
        }

        $monthData = $this->parseMonthFilter($request->input('mes'));
        $specificDate = $request->filled('fecha') ? Carbon::parse($request->input('fecha')) : null;
        $isToday = $request->boolean('today') || $request->input('mes') === 'hoy';
        $search = trim((string) $request->input('q', ''));

        return [
            'provincia' => $provincia,
            'province_id' => $provincia?->id,
            'province_slug' => $provincia?->slug,
            'month_label' => $monthData['label'],
            'month_slug' => $monthData['slug'],
            'month_start' => $monthData['start'],
            'month_end' => $monthData['end'],
            'is_today' => $isToday,
            'specific_date' => $specificDate,
            'interprete' => $interprete,
            'search' => $search !== '' ? $search : null,
        ];
    }

    private function parseMonthFilter(?string $value): array
    {
        if (! $value || $value === 'hoy') {
            return ['label' => null, 'slug' => null, 'start' => null, 'end' => null];
        }

        if (preg_match('/^\d{4}-\d{2}$/', $value)) {
            $date = Carbon::createFromFormat('Y-m', $value)->startOfMonth();
        } elseif (preg_match('/^([a-záéíóú]+)-(\d{4})$/u', Str::lower($value), $matches)) {
            $monthNumber = array_search(Str::ascii($matches[1]), array_map(fn ($month) => Str::ascii($month), self::MONTHS), true);
            if ($monthNumber === false) {
                return ['label' => null, 'slug' => null, 'start' => null, 'end' => null];
            }
            $date = Carbon::create((int) $matches[2], (int) $monthNumber, 1)->startOfMonth();
        } else {
            return ['label' => null, 'slug' => null, 'start' => null, 'end' => null];
        }

        return [
            'label' => $this->formatMonthLabel($date),
            'slug' => $this->formatMonthSlug($date),
            'start' => $date->copy()->startOfMonth(),
            'end' => $date->copy()->endOfMonth(),
        ];
    }

    private function buildSeoContent(array $filters): array
    {
        $provinceName = $filters['provincia']?->nombre;
        $monthLabel = $filters['month_label'];
        $today = $filters['is_today'];

        if ($provinceName && $today) {
            $heading = "Eventos de folklore hoy en {$provinceName}";
            $metaTitle = "Eventos de folklore en {$provinceName} hoy";
            $metaDescription = "Consulta la cartelera de folklore de hoy en {$provinceName}. Descubri peñas, festivales y shows con artistas en agenda y datos clave para asistir.";
            $introText = "La cartelera de hoy en {$provinceName} reune shows, peñas y encuentros folkloricos para quienes buscan propuestas vigentes y cercanas. En esta agenda vas a encontrar presentaciones con fecha confirmada, informacion de lugar, artistas vinculados y acceso rapido al detalle de cada evento.";

            return [$heading, $metaTitle, $metaDescription, $introText];
        }

        if ($provinceName && $monthLabel) {
            $heading = "Agenda folklorica en {$provinceName} - {$monthLabel}";
            $metaTitle = "Agenda folklore {$provinceName} {$monthLabel}";
            $metaDescription = "Explora la agenda folklorica de {$provinceName} para {$monthLabel}. Encontra festivales, peñas y recitales con fechas, lugares e interpretes destacados.";
            $introText = "Esta agenda folklorica de {$provinceName} para {$monthLabel} esta pensada para captar busquedas locales y planificar salidas con anticipacion. Reune eventos proximos con sus artistas, ciudades y datos de contexto para que el usuario compare opciones y descubra actividad cultural en la provincia.";

            return [$heading, $metaTitle, $metaDescription, $introText];
        }

        if ($provinceName) {
            $heading = "Eventos de folklore en {$provinceName}";
            $metaTitle = "Eventos de folklore en {$provinceName}";
            $metaDescription = "Descubri eventos de folklore en {$provinceName}: peñas, festivales y shows con fechas actualizadas, artistas participantes y acceso al detalle completo.";
            $introText = "La cartelera de eventos de folklore en {$provinceName} concentra propuestas culturales para quienes buscan musica en vivo, festivales populares y peñas durante todo el año. Desde esta pagina se accede a eventos activos con informacion clara sobre fecha, lugar, provincia e interpretes relacionados.";

            return [$heading, $metaTitle, $metaDescription, $introText];
        }

        $heading = 'Cartelera de eventos folkloricos';
        $metaTitle = 'Agenda folklore argentina: eventos, peñas y festivales';
        $metaDescription = 'Consulta la agenda del folklore argentino con eventos por provincia, por mes y por artista. Encontra peñas, festivales y shows actualizados.';
        $introText = 'La cartelera de Mi Folklore Argentino reune eventos, peñas y festivales de distintas provincias en una sola pagina. Podes navegar por ubicacion, mes, artista o fecha especifica para encontrar propuestas relevantes y descubrir nuevas actividades del circuito folklorico.';

        return [$heading, $metaTitle, $metaDescription, $introText];
    }

    private function buildCanonicalAndRobots(array $filters): array
    {
        $canonicalUrl = route('cartelera.index');
        $isIndexable = false;

        if ($filters['provincia'] && $filters['is_today'] && ! $filters['interprete'] && ! $filters['specific_date'] && ! $filters['search']) {
            $canonicalUrl = url('/cartelera-de-eventos-folkloricos/'.$filters['province_slug'].'/hoy');
            $isIndexable = true;
        } elseif ($filters['provincia'] && $filters['month_slug'] && ! $filters['interprete'] && ! $filters['specific_date'] && ! $filters['search']) {
            $canonicalUrl = url('/cartelera-de-eventos-folkloricos/'.$filters['province_slug'].'/'.$filters['month_slug']);
            $isIndexable = true;
        } elseif ($filters['provincia'] && ! $filters['interprete'] && ! $filters['specific_date'] && ! $filters['search'] && ! $filters['month_slug']) {
            $canonicalUrl = url('/cartelera-de-eventos-folkloricos/'.$filters['province_slug']);
            $isIndexable = true;
        } elseif (! $filters['provincia'] && ! $filters['interprete'] && ! $filters['specific_date'] && ! $filters['search'] && ! $filters['month_slug'] && ! $filters['is_today']) {
            $canonicalUrl = route('cartelera.index');
            $isIndexable = true;
        } elseif ($filters['provincia']) {
            $canonicalUrl = url('/cartelera-de-eventos-folkloricos/'.$filters['province_slug']);
        }

        return [$canonicalUrl, $isIndexable ? 'index,follow' : 'noindex,follow'];
    }

    private function buildBreadcrumbs(array $filters): array
    {
        $breadcrumbs = [
            ['label' => 'Cartelera', 'url' => route('cartelera.index')],
        ];

        if ($filters['provincia']) {
            $breadcrumbs[] = [
                'label' => $filters['provincia']->nombre,
                'url' => url('/cartelera-de-eventos-folkloricos/'.$filters['province_slug']),
            ];
        }

        if ($filters['is_today']) {
            $breadcrumbs[] = ['label' => 'Hoy'];
        } elseif ($filters['month_label']) {
            $breadcrumbs[] = ['label' => $filters['month_label']];
        }

        return $breadcrumbs;
    }

    private function buildMonthOptions(): Collection
    {
        return collect(range(0, 11))->map(function (int $offset) {
            $date = now()->startOfMonth()->addMonths($offset);

            return [
                'value' => $this->formatMonthSlug($date),
                'label' => $this->formatMonthLabel($date),
            ];
        });
    }

    private function formatMonthSlug(Carbon $date): string
    {
        return self::MONTHS[(int) $date->month].'-'.$date->year;
    }

    private function formatMonthLabel(Carbon $date): string
    {
        return ucfirst(self::MONTHS[(int) $date->month]).' '.$date->year;
    }

    private function findProvinciaBySlug(string $slug): ?Provincia
    {
        return Provincia::all()->first(function (Provincia $provincia) use ($slug) {
            return $provincia->slug === Str::slug($slug);
        });
    }
}
