<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Festival;
use App\Models\Locality;
use App\Models\Mes;
use App\Models\News;
use App\Models\Provincia;
use App\Support\CanonicalUrl;
use App\Support\SeoMetadata;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class FestivalesController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $this->extractFilters($request);
        $query = $this->publishedFestivalQuery()->with($this->festivalCardRelations());

        $this->applyFilters($query, $filters);

        $results = $query
            ->orderByRaw('COALESCE(mes_id, 999) asc')
            ->orderBy('title')
            ->paginate(12)
            ->withQueryString();

        $currentMonthId = (int) now()->format('n');
        $hasFilters = (bool) array_filter([
            $filters['search'],
            $filters['province_id'],
            $filters['locality_id'],
            $filters['month_id'],
        ]);

        $featured = $hasFilters
            ? collect()
            : Cache::remember('festivales:index:featured', 600, function () {
                return $this->publishedFestivalQuery()
                    ->with($this->festivalCardRelations())
                    ->orderByDesc('visitas')
                    ->take(3)
                    ->get();
            });

        $currentMonthFestivals = $hasFilters
            ? collect()
            : Cache::remember('festivales:index:current-month:'.$currentMonthId, 600, function () use ($currentMonthId) {
                return $this->publishedFestivalQuery()
                    ->with($this->festivalCardRelations())
                    ->where('mes_id', $currentMonthId)
                    ->orderBy('title')
                    ->take(4)
                    ->get();
            });

        $availableProvinces = Cache::remember('festivales:index:provincias', 3600, fn () => Provincia::orderBy('nombre')->get());
        $availableMonths = Cache::remember('festivales:index:meses', 3600, fn () => Mes::orderBy('id')->get());
        $availableLocalities = Locality::query()
            ->when($filters['province_id'], fn ($query) => $query->where('province_id', $filters['province_id']))
            ->orderBy('name')
            ->get();

        $provinceLinks = Cache::remember('festivales:index:province-link-ids', 600, function () {
            return $this->publishedFestivalQuery()
                ->select('province_id')
                ->distinct()
                ->get()
                ->pluck('province_id')
                ->filter()
                ->unique()
                ->values();
        });

        $monthLinks = Cache::remember('festivales:index:month-link-ids', 600, function () {
            return $this->publishedFestivalQuery()
                ->select('mes_id')
                ->distinct()
                ->get()
                ->pluck('mes_id')
                ->filter()
                ->unique()
                ->values();
        });

        $relatedNews = $hasFilters
            ? collect()
            : Cache::remember('festivales:index:related-news', 600, function () {
                return News::query()
                    ->publishedVisible()
                    ->whereHas('festivales')
                    ->with(['categoria:id,nombre', 'images'])
                    ->latest('published_at')
                    ->take(3)
                    ->get();
            });

        $relatedEvents = $hasFilters
            ? collect()
            : Cache::remember('festivales:index:related-events', 600, function () {
                return Event::query()
                    ->publishedVisible()
                    ->whereHas('festivales')
                    ->with(['interpretes:id,interprete,slug', 'images', 'provincia:id,nombre'])
                    ->orderBy('start_at')
                    ->take(3)
                    ->get();
            });

        [$canonical, $robots, $headline, $intro] = $this->indexSeoContext($filters);

        return view('frontend.festivales.index', [
            'featured' => $featured,
            'currentMonthFestivals' => $currentMonthFestivals,
            'results' => $results,
            'filters' => $filters,
            'availableProvinces' => $availableProvinces,
            'availableMonths' => $availableMonths,
            'availableLocalities' => $availableLocalities,
            'provinceLinks' => $this->buildProvinceLinks($provinceLinks),
            'monthLinks' => $this->buildMonthLinks($monthLinks),
            'relatedNews' => $relatedNews,
            'relatedEvents' => $relatedEvents,
            'metaTitle' => $headline.' | Mi Folklore Argentino',
            'metaDescription' => $intro,
            'metaRobots' => $robots,
            'canonical' => $canonical,
            'h1' => $headline,
            'introText' => $intro,
            'breadcrumbs' => [
                ['label' => 'Festivales', 'url' => route('festivales.index')],
            ],
        ]);
    }

    public function province(string $provinceSlug): View
    {
        $province = Provincia::all()->first(fn ($item) => $item->slug === $provinceSlug);
        abort_if(! $province, 404);

        $results = $this->publishedFestivalQuery()
            ->with($this->festivalCardRelations())
            ->where('province_id', $province->id)
            ->orderByRaw('COALESCE(mes_id, 999) asc')
            ->orderBy('title')
            ->paginate(12);

        abort_if($results->total() === 0, 404);

        $activeMonthIds = $this->publishedFestivalQuery()
            ->where('province_id', $province->id)
            ->get()
            ->pluck('mes_id')
            ->filter()
            ->unique()
            ->values();

        return view('frontend.festivales.index', [
            'featured' => collect(),
            'currentMonthFestivals' => collect(),
            'results' => $results,
            'filters' => ['province_slug' => $provinceSlug, 'month_slug' => null, 'search' => null, 'province_id' => $province->id, 'month_id' => null, 'locality_id' => null],
            'availableProvinces' => Provincia::orderBy('nombre')->get(),
            'availableMonths' => Mes::orderBy('id')->get(),
            'availableLocalities' => Locality::where('province_id', $province->id)->orderBy('name')->get(),
            'provinceLinks' => $this->buildProvinceLinks(collect([$province->id]), $province->id),
            'monthLinks' => $this->buildMonthLinks($activeMonthIds),
            'relatedNews' => collect(),
            'relatedEvents' => collect(),
            'metaTitle' => 'Festivales de folklore en '.$province->nombre.' | Mi Folklore Argentino',
            'metaDescription' => 'Explora festivales y fiestas tradicionales del folklore argentino en '.$province->nombre.'.',
            'metaRobots' => 'index,follow',
            'canonical' => route('festivales.province', $provinceSlug),
            'h1' => 'Festivales de folklore en '.$province->nombre,
            'introText' => 'Listado de festivales publicados vinculados a '.$province->nombre.'.',
            'breadcrumbs' => [
                ['label' => 'Festivales', 'url' => route('festivales.index')],
                ['label' => $province->nombre],
            ],
        ]);
    }

    public function month(string $monthSlug): View
    {
        $month = Mes::all()->first(fn ($item) => str($item->nombre)->slug()->toString() === $monthSlug);
        abort_if(! $month, 404);

        $results = $this->publishedFestivalQuery()
            ->with($this->festivalCardRelations())
            ->where('mes_id', $month->id)
            ->orderBy('title')
            ->paginate(12);

        abort_if($results->total() === 0, 404);

        $activeProvinceIds = $this->publishedFestivalQuery()
            ->where('mes_id', $month->id)
            ->get()
            ->pluck('province_id')
            ->filter()
            ->unique()
            ->values();

        return view('frontend.festivales.index', [
            'featured' => collect(),
            'currentMonthFestivals' => collect(),
            'results' => $results,
            'filters' => ['province_slug' => null, 'month_slug' => $monthSlug, 'search' => null, 'province_id' => null, 'month_id' => $month->id, 'locality_id' => null],
            'availableProvinces' => Provincia::orderBy('nombre')->get(),
            'availableMonths' => Mes::orderBy('id')->get(),
            'availableLocalities' => collect(),
            'provinceLinks' => $this->buildProvinceLinks($activeProvinceIds),
            'monthLinks' => $this->buildMonthLinks(collect([$month->id]), $month->id),
            'relatedNews' => collect(),
            'relatedEvents' => collect(),
            'metaTitle' => 'Festivales de folklore en '.$month->nombre.' | Mi Folklore Argentino',
            'metaDescription' => 'Explora festivales y fiestas tradicionales del folklore argentino durante '.$month->nombre.'.',
            'metaRobots' => 'index,follow',
            'canonical' => route('festivales.month', $monthSlug),
            'h1' => 'Festivales de folklore en '.$month->nombre,
            'introText' => 'Listado de festivales publicados vinculados al mes de '.$month->nombre.'.',
            'breadcrumbs' => [
                ['label' => 'Festivales', 'url' => route('festivales.index')],
                ['label' => $month->nombre],
            ],
        ]);
    }

    public function provinceMonth(string $provinceSlug, string $monthSlug): View
    {
        $province = Provincia::all()->first(fn ($item) => $item->slug === $provinceSlug);
        $month = Mes::all()->first(fn ($item) => str($item->nombre)->slug()->toString() === $monthSlug);
        abort_if(! $province || ! $month, 404);

        $results = $this->publishedFestivalQuery()
            ->with($this->festivalCardRelations())
            ->where('province_id', $province->id)
            ->where('mes_id', $month->id)
            ->orderBy('title')
            ->paginate(12);

        abort_if($results->total() === 0, 404);

        $minimum = (int) config('festivals.province_month_indexable_minimum', 3);
        $indexable = $results->total() >= $minimum;

        $activeProvinceIds = $this->publishedFestivalQuery()
            ->where('mes_id', $month->id)
            ->pluck('province_id')
            ->filter()
            ->unique()
            ->values();

        $activeMonthIds = $this->publishedFestivalQuery()
            ->where('province_id', $province->id)
            ->pluck('mes_id')
            ->filter()
            ->unique()
            ->values();

        return view('frontend.festivales.index', [
            'featured' => collect(),
            'currentMonthFestivals' => collect(),
            'results' => $results,
            'filters' => ['province_slug' => $provinceSlug, 'month_slug' => $monthSlug, 'search' => null, 'province_id' => $province->id, 'month_id' => $month->id, 'locality_id' => null],
            'availableProvinces' => Provincia::orderBy('nombre')->get(),
            'availableMonths' => Mes::orderBy('id')->get(),
            'availableLocalities' => Locality::where('province_id', $province->id)->orderBy('name')->get(),
            'provinceLinks' => $this->buildProvinceLinks($activeProvinceIds, $province->id, $month->slug ?? str($month->nombre)->slug()->toString()),
            'monthLinks' => $this->buildMonthLinks($activeMonthIds, $month->id, $province->slug),
            'relatedNews' => collect(),
            'relatedEvents' => collect(),
            'metaTitle' => 'Festivales de folklore en '.$province->nombre.' durante '.$month->nombre.' | Mi Folklore Argentino',
            'metaDescription' => 'Explora festivales y fiestas tradicionales del folklore argentino en '.$province->nombre.' durante '.$month->nombre.'.',
            'metaRobots' => $indexable ? 'index,follow' : 'noindex,follow',
            'canonical' => route('festivales.province-month', [$provinceSlug, $monthSlug]),
            'h1' => 'Festivales de folklore en '.$province->nombre.' durante '.$month->nombre,
            'introText' => 'Listado de festivales publicados para '.$province->nombre.' durante '.$month->nombre.'.',
            'breadcrumbs' => [
                ['label' => 'Festivales', 'url' => route('festivales.index')],
                ['label' => $province->nombre, 'url' => route('festivales.province', $provinceSlug)],
                ['label' => $month->nombre],
            ],
        ]);
    }

    public function show(string $slug): View
    {
        $festival = $this->publishedFestivalQuery()
            ->with([
                'images',
                'provincia',
                'locality',
                'mes',
                'noticias' => fn ($query) => $query
                    ->publishedVisible()
                    ->with(['categoria', 'images', 'interprete.images'])
                    ->latest('published_at'),
                'events' => fn ($query) => $query
                    ->publishedVisible()
                    ->where('start_at', '>=', now())
                    ->with(['interpretes.images', 'images', 'provincia'])
                    ->orderBy('start_at'),
                'interpretes' => fn ($query) => $query
                    ->where('estado', 1)
                    ->with('images')
                    ->orderBy('interprete'),
                'knowledgeArticles' => fn ($query) => $query
                    ->visible()
                    ->with(['category', 'images'])
                    ->latest('published_at'),
            ])
            ->where('slug', $slug)
            ->firstOrFail();

        $festival->increment('visitas');

        $sameProvince = $this->publishedFestivalQuery()
            ->where('province_id', $festival->province_id)
            ->where('id', '<>', $festival->id)
            ->with($this->festivalCardRelations())
            ->take(3)
            ->get();

        $sameMonth = $this->publishedFestivalQuery()
            ->where('mes_id', $festival->mes_id)
            ->where('id', '<>', $festival->id)
            ->with($this->festivalCardRelations())
            ->take(3)
            ->get();

        $seo = SeoMetadata::festival($festival);
        $journey = app(\App\Services\Product\FestivalJourneyService::class)->forFestival($festival);

        return view('frontend.festivales.show', [
            'festival' => $festival,
            'journey' => $journey,
            'sameProvince' => $sameProvince,
            'sameMonth' => $sameMonth,
            'filters' => ['province_slug' => null, 'month_slug' => null, 'search' => null, 'province_id' => null, 'month_id' => null, 'locality_id' => null],
            'availableProvinces' => Provincia::orderBy('nombre')->get(),
            'availableMonths' => Mes::orderBy('id')->get(),
            'availableLocalities' => Locality::orderBy('name')->get(),
            'metaTitle' => $seo['title'],
            'metaDescription' => $seo['description'],
            'metaRobots' => 'index,follow',
            'canonical' => route('festivales.show', $festival->slug),
            'h1' => $seo['h1'],
            'filtersHeading' => 'Festivales y fiestas tradicionales del folklore argentino',
            'filtersIntro' => 'Explora festivales de folklore argentino por provincia, mes y búsquedas combinadas desde una home navegable y evergreen.',
            'breadcrumbs' => [
                ['label' => 'Festivales', 'url' => route('festivales.index')],
                ['label' => $festival->provincia?->nombre, 'url' => $festival->provincia ? route('festivales.province', $festival->provincia->slug) : null],
                ['label' => $festival->mes?->nombre, 'url' => $festival->mes ? route('festivales.month', str($festival->mes->nombre)->slug()) : null],
                ['label' => $festival->title],
            ],
        ]);
    }

    private function publishedFestivalQuery(): Builder
    {
        return Festival::query()->publishedVisible();
    }

    private function festivalCardRelations(): array
    {
        return ['images', 'interpretes.images', 'events.images', 'provincia', 'mes', 'locality'];
    }

    private function extractFilters(Request $request): array
    {
        return [
            'search' => $request->string('q')->toString() ?: null,
            'province_id' => $request->integer('province_id') ?: null,
            'locality_id' => $request->integer('locality_id') ?: null,
            'month_id' => $request->integer('mes_id') ?: null,
            'province_slug' => null,
            'month_slug' => null,
        ];
    }

    private function applyFilters(Builder $query, array $filters): void
    {
        if ($filters['search']) {
            $query->where('title', 'like', '%'.$filters['search'].'%');
        }

        if ($filters['province_id']) {
            $query->where('province_id', $filters['province_id']);
        }

        if ($filters['locality_id']) {
            $query->where('locality_id', $filters['locality_id']);
        }

        if ($filters['month_id']) {
            $query->where('mes_id', $filters['month_id']);
        }
    }

    private function indexSeoContext(array $filters): array
    {
        $headline = 'Festivales y fiestas tradicionales del folklore argentino';
        $description = 'Explora festivales de folklore argentino por provincia, mes y busquedas combinadas desde una home navegable y evergreen.';
        $canonical = CanonicalUrl::normalize(route('festivales.index'));
        $robots = array_filter($filters) ? 'noindex,follow' : 'index,follow';

        if ($filters['province_id'] && ! $filters['search'] && ! $filters['locality_id'] && $filters['month_id']) {
            $province = Provincia::find($filters['province_id']);
            $month = Mes::find($filters['month_id']);
            if ($province && $month) {
                $headline = 'Festivales de folklore en '.$province->nombre.' durante '.$month->nombre;
                $description = 'Consulta festivales de folklore en '.$province->nombre.' durante '.$month->nombre.'.';
                $canonical = CanonicalUrl::normalize(route('festivales.province-month', [$province->slug, str($month->nombre)->slug()]));
            }
        } elseif ($filters['province_id'] && ! $filters['search'] && ! $filters['locality_id']) {
            $province = Provincia::find($filters['province_id']);
            if ($province) {
                $headline = 'Festivales de folklore en '.$province->nombre;
                $description = 'Consulta festivales de folklore en '.$province->nombre.'.';
                $canonical = CanonicalUrl::normalize(route('festivales.province', $province->slug));
            }
        } elseif ($filters['month_id'] && ! $filters['search'] && ! $filters['locality_id']) {
            $month = Mes::find($filters['month_id']);
            if ($month) {
                $headline = 'Festivales de folklore en '.$month->nombre;
                $description = 'Consulta festivales de folklore durante '.$month->nombre.'.';
                $canonical = CanonicalUrl::normalize(route('festivales.month', str($month->nombre)->slug()));
            }
        }

        return [$canonical, $robots, $headline, $description];
    }

    private function buildProvinceLinks($activeProvinceIds, ?int $currentProvinceId = null, ?string $monthSlug = null)
    {
        $activeProvinceIds = collect($activeProvinceIds)->map(fn ($id) => (int) $id)->all();

        return Provincia::orderBy('nombre')
            ->get()
            ->map(function (Provincia $province) use ($activeProvinceIds, $currentProvinceId, $monthSlug) {
                $enabled = in_array((int) $province->id, $activeProvinceIds, true);
                $url = null;

                if ($enabled) {
                    $url = $monthSlug
                        ? route('festivales.province-month', [$province->slug, $monthSlug])
                        : route('festivales.province', $province->slug);
                }

                return (object) [
                    'label' => $province->nombre,
                    'enabled' => $enabled,
                    'active' => $currentProvinceId === (int) $province->id,
                    'url' => $url,
                ];
            });
    }

    private function buildMonthLinks($activeMonthIds, ?int $currentMonthId = null, ?string $provinceSlug = null)
    {
        $activeMonthIds = collect($activeMonthIds)->map(fn ($id) => (int) $id)->all();

        return Mes::orderBy('id')
            ->get()
            ->map(function (Mes $month) use ($activeMonthIds, $currentMonthId, $provinceSlug) {
                $enabled = in_array((int) $month->id, $activeMonthIds, true);
                $slug = str($month->nombre)->slug()->toString();
                $url = null;

                if ($enabled) {
                    $url = $provinceSlug
                        ? route('festivales.province-month', [$provinceSlug, $slug])
                        : route('festivales.month', $slug);
                }

                return (object) [
                    'label' => $month->nombre,
                    'enabled' => $enabled,
                    'active' => $currentMonthId === (int) $month->id,
                    'url' => $url,
                ];
            });
    }
}
