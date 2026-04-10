<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Interprete;
use App\Models\Event;
use Illuminate\Http\Request;
use Psy\Util\Str;

class ShowsController extends Controller
{

    public function index(Request $request)
    {
        $query = Event::where('editorial_status', 'approved')
            ->where('start_at', '>=', now());

        if ($request->filled('mes')) {
            $query->whereMonth('start_at', $request->mes);
        }

        if ($request->filled('provincia_id')) {
            $query->where('provincia_id', $request->provincia_id);
        }

        if ($request->filled('interprete_id')) {
            $query->whereHas('interpretes', function($q) use($request) {
                $q->where('interpretes.id', $request->interprete_id);
            });
        }

        $shows = $query->with(['interpretes', 'images'])->orderBy('start_at')->paginate(12);

        $interpretes = \App\Models\Interprete::orderBy('interprete')->get();
        $provincias = \App\Models\Provincia::orderBy('nombre')->get();

        $sinResultados = $shows->count() === 0;

        $metaTitle = "Cartelera de Eventos del Folklore Argentino: Festivales y Shows";
        $metaDescription = "Consulta la cartelera de eventos del folklore argentino. Encuentra información sobre festivales, conciertos y shows en todo el país. ¡Mantente al día con nuestra agenda!";

        $breadcrumbs = [
            ['label' => 'Cartelera', 'url' => route('cartelera.index')]
        ];

        return view('frontend.shows.index', compact(
            'shows',
            'interpretes',
            'provincias',
            'metaTitle',
            'metaDescription',
            'sinResultados',
            'breadcrumbs'
        ));
    }


    public function byArtista($slug)
    {
        // dd($slug);
        $interprete = Interprete::where('slug', $slug)->first();
        $shows = $interprete->events()->with('images')->get();
        $interpretes = Interprete::getInterpretesExcluding($interprete->id);

        $section = 'shows';

        $metaTitle = "Shows de " . $interprete->interprete;
        $metaDescription = "Cartelera de shows de " . $interprete->interprete . ", interprete del folklore argentino";

        $breadcrumbs = [
            ['label' => 'Artistas', 'url' => route('interpretes.index')],
            ['label' => $interprete->interprete, 'url' => route('artista.show', $interprete->slug)],
            ['label' => 'Shows']
        ];

        return view('frontend.shows.byArtista', compact('shows', 'interprete', 'interpretes', 'section', 'metaTitle', 'metaDescription', 'breadcrumbs'));
    }

    public function show($slug)
    {
        $show = Event::with(['interpretes', 'provincia', 'images'])->where('slug', $slug)->firstOrFail();

        $ultimos_shows = Event::where('editorial_status', 'approved')
            ->where('id', '<>', $show->id)
            ->orderByDesc('created_at')
            ->take(10)
            ->get();

        // Opcional: cargar noticias relacionadas si hay relación definida
        $noticiasRelacionadas = $show->noticias ?? collect();

        $metaTitle = $show->titulo . ' - Show de folklore argentino';
        $metaDescription = \Illuminate\Support\Str::limit(strip_tags($show->detalles), 150);

        $breadcrumbs = [
            ['label' => 'Cartelera', 'url' => route('cartelera.index')],
            ['label' => $show->titulo]
        ];

        return view('frontend.shows.show', compact(
            'show',
            'ultimos_shows',
            'metaTitle',
            'metaDescription',
            'noticiasRelacionadas',
            'breadcrumbs'
        ));
    }


    public function showGeneral($slug)
    {
        $show = Event::where('slug', $slug)->with('interpretes')->firstOrFail();

        $canonical = $show->interpretes->count() > 0
            ? route('artista.show.detalle', [$show->interpretes->first()->slug, $show->slug])
            : route('cartelera.show', $show->slug);

        return view('frontend.shows.show', compact('show', 'canonical'));
    }

}
