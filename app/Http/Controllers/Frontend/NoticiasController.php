<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Cancion;
use App\Models\Categoria;
use App\Models\Disco;
use App\Models\Foto;
use App\Models\Interprete;
use App\Models\News;
use App\Models\Show;
use App\Models\Video;
use App\Services\LinkService;
use App\Support\SeoMetadata;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class NoticiasController extends Controller
{
    protected $linkService;

    public function __construct(LinkService $linkService)
    {
        $this->linkService = $linkService;
    }

    public function index()
    {
        $ultimas = News::where('editorial_status', 'published')
            ->with(['categoria', 'images'])
            ->latest()
            ->paginate(16);

        $ultimasSidebar = News::where('editorial_status', 'published')
            ->with(['categoria', 'interprete', 'images'])
            ->latest()
            ->take(10)
            ->get();

        $categorias = Categoria::get();

        $metaTitle = 'Noticias de Folklore Argentino: Novedades y Eventos Recientes';
        $metaDescription = 'Descubre las ultimas noticias del folklore argentino. Mantente al tanto de eventos, festivales y novedades culturales relevantes.';

        $breadcrumbs = [
            ['label' => 'Noticias', 'url' => route('noticias.index')],
        ];

        return view('frontend.noticias.index', compact('ultimas', 'categorias', 'ultimasSidebar', 'metaTitle', 'metaDescription', 'breadcrumbs'));
    }

    public function noticias(Interprete $interprete)
    {
        $noticias = News::where('editorial_status', 'published')
            ->where(function ($query) use ($interprete) {
                $query->where('interprete_id', $interprete->id)
                    ->orWhereHas('interpretes', function ($q) use ($interprete) {
                        $q->where('interprete_id', $interprete->id);
                    });
            })
            ->with(['images', 'categoria'])
            ->orderBy('created_at', 'desc')
            ->distinct()
            ->paginate(10);

        $interpretes = Interprete::getInterpretesExcluding($interprete->id);
        $section = 'noticias';

        $metaTitle = 'Noticias de '.$interprete->interprete.' - Mi Folklore Argentino';
        $metaDescription = "Todas las noticias de {$interprete->interprete}: presentaciones, novedades y mas del folklore argentino.";

        $breadcrumbs = [
            ['label' => 'Noticias', 'url' => route('noticias.index')],
            ['label' => $interprete->interprete],
        ];

        return view('frontend.noticias.byArtista', compact('noticias', 'interprete', 'interpretes', 'section', 'metaTitle', 'metaDescription', 'breadcrumbs'));
    }

    public function byArtista($slug)
    {
        $interprete = Interprete::where('slug', $slug)->first();
        $noticias = $interprete->noticias()->with('images')->where('editorial_status', 'published')->get();
        $interpretes = Interprete::getInterpretesExcluding($interprete->id);
        $section = 'noticias';

        $metaTitle = 'Noticias de '.$interprete->interprete.' - Mi Folklore Argentino';
        $metaDescription = "Todas las noticias de {$interprete->interprete}: presentaciones, novedades y mas del folklore argentino.";

        $breadcrumbs = [
            ['label' => 'Noticias', 'url' => route('noticias.index')],
            ['label' => $interprete->interprete],
        ];

        return view('frontend.noticias.byArtista', compact('noticias', 'interprete', 'interpretes', 'section', 'metaTitle', 'metaDescription', 'breadcrumbs'));
    }

    public function byCategoria($slug)
    {
        $categoria = Categoria::where('slug', $slug)->firstOrFail();

        $noticias = News::where('categoria_id', $categoria->id)
            ->where('editorial_status', 'published')
            ->with(['interpretes', 'images'])
            ->latest()
            ->paginate(10);

        $ultimas = News::where('editorial_status', 'published')
            ->with('images')
            ->orderByDesc('created_at')
            ->take(5)
            ->get();

        $section = 'noticias';
        $categorias = Categoria::get();

        $metaTitle = 'Noticias de '.$categoria->nombre.' del Folklore Argentino';
        $metaDescription = "Todas las noticias de {$categoria->nombre} del folklore argentino: presentaciones en vivo, lanzamientos recientes, artistas en agenda y hechos destacados del genero.";

        return view('frontend.noticias.byCategoria', compact('categoria', 'categorias', 'noticias', 'ultimas', 'section', 'metaTitle', 'metaDescription'));
    }

    public function show($param1, $param2 = null)
    {
        if ($param2 !== null) {
            $slugNoticia = $param2;
            $slugIterprete = $param1;
        } else {
            $slugNoticia = $param1;
            $slugIterprete = null;
        }

        $interpretes = collect();

        if ($slugIterprete) {
            $interprete = Interprete::where('slug', $slugIterprete)->first();

            if ($interprete) {
                $interpretes = Interprete::getInterpretesExcluding($interprete->id);
            }
        } else {
            $interprete = null;
        }

        $noticia = News::query()
            ->with(['categoria', 'interprete', 'interpretes', 'images'])
            ->where('slug', $slugNoticia)
            ->where('editorial_status', 'published')
            ->where(function (Builder $query) {
                $query->whereNull('published_at')
                    ->orWhere('published_at', '<=', now());
            })
            ->firstOrFail();

        $ultimas_noticias = News::query()
            ->with(['interprete', 'categoria', 'images'])
            ->where('editorial_status', 'published')
            ->where(function (Builder $query) {
                $query->whereNull('published_at')
                    ->orWhere('published_at', '<=', now());
            })
            ->where('id', '<>', $noticia->id)
            ->orderByDesc('created_at')
            ->take(10)
            ->get();

        $noticia->increment('visitas');

        $relacionadas = News::query()
            ->where('editorial_status', 'published')
            ->where(function (Builder $query) {
                $query->whereNull('published_at')
                    ->orWhere('published_at', '<=', now());
            })
            ->where('id', '<>', $noticia->id)
            ->where(function ($query) use ($noticia) {
                if ($noticia->categoria_id) {
                    $query->where('categoria_id', $noticia->categoria_id);
                }

                if ($noticia->interprete_id) {
                    $query->orWhere('interprete_id', $noticia->interprete_id);
                }

                $interpreteIds = $noticia->interpretes->pluck('id')->toArray();
                if (! empty($interpreteIds)) {
                    $query->orWhereHas('interpretes', function ($q) use ($interpreteIds) {
                        $q->whereIn('interprete_id', $interpreteIds);
                    });
                }
            })
            ->with(['categoria', 'interprete', 'images'])
            ->orderByDesc('created_at')
            ->distinct()
            ->take(3)
            ->get();

        $seo = SeoMetadata::news($noticia);
        $metaTitle = $seo['title'];
        $metaDescription = $seo['description'];
        $h1 = $seo['h1'];

        $breadcrumbs = [
            ['label' => 'Noticias', 'url' => route('noticias.index')],
            ['label' => $noticia->titulo],
        ];

        return view('frontend.noticias.show', compact('noticia', 'interprete', 'interpretes', 'ultimas_noticias', 'metaTitle', 'metaDescription', 'h1', 'relacionadas', 'breadcrumbs'));
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
}
