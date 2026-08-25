<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Interprete;
use App\Services\LinkService;
use App\Support\SeoMetadata;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class InterpretesController extends Controller
{
    protected $linkService;

    public function __construct(LinkService $linkService)
    {
        $this->linkService = $linkService;
    }

    public function index()
    {
        $interpretes = Interprete::query()
            ->where('estado', 1)
            ->select(['id', 'interprete', 'slug', 'foto', 'biografia', 'visitas'])
            ->with('images')
            ->orderBy('interprete', 'asc')
            ->simplePaginate(12);

        $alphabet = range('a', 'z');
        $metaTitle = 'Biografias de Artistas del Folklore Argentino: Historia y Trayectoria';
        $metaDescription = 'Conoce la historia y trayectoria de los artistas e interpretes del folklore argentino. Descubre sus biografias completas y su contribucion a la musica tradicional.';

        $breadcrumbs = [
            ['label' => 'Artistas', 'url' => route('interpretes.index')],
        ];

        return view('frontend.interpretes.index', compact('interpretes', 'metaTitle', 'metaDescription', 'breadcrumbs', 'alphabet'));
    }

    public function biografia($slug)
    {
        $interprete = Interprete::where('slug', $slug)->with('images')->firstOrFail();
        $interpretes = Interprete::getInterpretesExcluding($interprete->id);
        $section = 'biografias';

        $interprete->increment('visitas');

        $recursos = [
            'Noticias' => 5,
            'Shows' => 2,
            'Discos' => 6,
            'Canciones' => 78,
            'Fotos' => 0,
            'Videos' => 3,
        ];

        $seo = SeoMetadata::biography($interprete);
        $metaTitle = $seo['title'];
        $metaDescription = $seo['description'];
        $h1 = $seo['h1'];

        $interprete->biografia = Cache::remember(
            'interprete:'.$interprete->id.':linked-biografia:'.optional($interprete->updated_at)->timestamp,
            3600,
            fn () => $this->linkService->autoLinkArtists($interprete->biografia)
        );

        $breadcrumbs = [
            ['label' => 'Artistas', 'url' => route('interpretes.index')],
            ['label' => $interprete->interprete, 'url' => route('artista.show', $interprete->slug)],
            ['label' => 'Biografia'],
        ];

        return view('frontend.interpretes.show', compact('interprete', 'interpretes', 'section', 'recursos', 'metaTitle', 'metaDescription', 'h1', 'breadcrumbs'));
    }

    public function show(Interprete $interprete)
    {
        $interprete->load('images');
        $noticias = $interprete->noticiasRelacionadas()
            ->latest('published_at')
            ->latest('created_at')
            ->take(3)
            ->get();
        $canciones = $interprete->canciones()->with('interprete')->latest()->take(3)->get();
        $discos = $interprete->discos()->with('images')->orderByDesc('anio')->take(3)->get();
        $shows = $interprete->events()
            ->publishedVisible()
            ->where('start_at', '>=', now())
            ->orderBy('start_at')
            ->with('images')
            ->take(2)
            ->get();
        $interpretes = Interprete::getInterpretesExcluding($interprete->id);

        $seo = SeoMetadata::artist($interprete);
        $metaTitle = $seo['title'];
        $metaDescription = $seo['description'];
        $h1 = $seo['h1'];

        $breadcrumbs = [
            ['label' => 'Artistas', 'url' => route('interpretes.index')],
            ['label' => $interprete->interprete],
        ];

        return view('frontend.interpretes.home-artista', compact(
            'interprete',
            'noticias',
            'canciones',
            'discos',
            'shows',
            'interpretes',
            'metaTitle',
            'metaDescription',
            'h1',
            'breadcrumbs'
        ));
    }

    public function busqueda(Request $request)
    {
        $term = $request->input('q');
        $interpretes = Interprete::where('estado', 1)
            ->where('interprete', 'LIKE', "%$term%")
            ->limit(10)
            ->get(['id', 'interprete', 'slug']);

        $results = [];

        foreach ($interpretes as $interprete) {
            $results[] = [
                'id' => $interprete->id,
                'text' => $interprete->interprete,
            ];
        }

        return response()->json($results);
    }

    public function letra($letra)
    {
        $letra = Str::lower($letra);

        $interpretes = Interprete::where('estado', 1)
            ->select(['id', 'interprete', 'slug', 'foto', 'biografia', 'visitas'])
            ->with('images')
            ->whereRaw('LOWER(interprete) LIKE ?', [$letra.'%'])
            ->orderBy('interprete', 'asc')
            ->simplePaginate(12);

        $alphabet = range('a', 'z');
        $metaTitle = "Biografias de interpretes folkloricos de Argentina que comienzan con {$letra}";
        $metaDescription = "Biografias de interpretes folkloricos de Argentina que comienzan con la letra {$letra}.";

        $breadcrumbs = [
            ['label' => 'Artistas', 'url' => route('interpretes.index')],
            ['label' => 'Letra '.strtoupper($letra)],
        ];

        return view('frontend.interpretes.letra', compact('interpretes', 'letra', 'metaTitle', 'metaDescription', 'breadcrumbs', 'alphabet'));
    }

    public function buscar(Request $request)
    {
        $searchTerm = $request->input('search');
        $searchColumns = ['title', 'description'];

        $resultados = Interprete::search(Interprete::class, $searchTerm, $searchColumns);

        return view('frontend.interpretes.resultados', compact('resultados', 'searchTerm'));
    }
}
