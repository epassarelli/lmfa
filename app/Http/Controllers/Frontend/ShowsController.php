<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Interprete;
use App\Models\Show;
use Illuminate\Http\Request;


class ShowsController extends Controller
{

    public function index(Request $request)
    {
        $query = Show::where('estado', 1)
            ->where('fecha', '>=', now());

        if ($request->filled('mes')) {
            $query->whereMonth('fecha', $request->mes);
        }

        if ($request->filled('provincia_id')) {
            $query->where('provincia_id', $request->provincia_id);
        }

        if ($request->filled('interprete_id')) {
            $query->where('interprete_id', $request->interprete_id);
        }

        $shows = $query->orderBy('fecha')->paginate(12);

        $interpretes = \App\Models\Interprete::orderBy('interprete')->get();
        $provincias = \App\Models\Provincia::orderBy('nombre')->get();

        $sinResultados = $shows->count() === 0;

        $metaTitle = "Cartelera de Eventos del Folklore Argentino: Festivales y Shows";
        $metaDescription = "Consulta la cartelera de eventos del folklore argentino. Encuentra información sobre festivales, conciertos y shows en todo el país. ¡Mantente al día con nuestra agenda!";

        return view('frontend.shows.index', compact(
            'shows',
            'interpretes',
            'provincias',
            'metaTitle',
            'metaDescription',
            'sinResultados'
        ));
    }


    public function byArtista($slug)
    {
        // dd($slug);
        $interprete = Interprete::where('slug', $slug)->first();
        $shows = $interprete->shows()->get();
        $interpretes = Interprete::getInterpretesExcluding($interprete->id);

        $section = 'shows';

        $metaTitle = "Shows de " . $interprete->interprete;
        $metaDescription = "Cartelera de shows de " . $interprete->interprete . ", interprete del folklore argentino";
        return view('frontend.shows.byArtista', compact('shows', 'interprete', 'interpretes', 'section', 'metaTitle', 'metaDescription'));
    }

    public function show($slug)
    {
        $show = Show::with(['interprete', 'provincia'])->where('slug', $slug)->firstOrFail();

        $ultimos_shows = Show::where('estado', 1)
            ->where('id', '<>', $show->id)
            ->orderByDesc('created_at')
            ->take(10)
            ->get();

        // Opcional: cargar noticias relacionadas si hay relación definida
        $noticiasRelacionadas = $show->noticias ?? collect();

        $metaTitle = $show->titulo . ' - Show de folklore argentino';
        $metaDescription = Str::limit(strip_tags($show->detalles), 150);

        return view('frontend.shows.show', compact(
            'show',
            'ultimos_shows',
            'metaTitle',
            'metaDescription',
            'noticiasRelacionadas'
        ));
    }
}
