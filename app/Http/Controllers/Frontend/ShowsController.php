<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Interprete;
use App\Models\Show;
use Illuminate\Http\Request;

class ShowsController extends Controller
{
    public function index()
    {
        $shows = Show::where('estado', 1)
            ->where('fecha', '>=', now())
            ->orderBy('publicar', 'desc')
            ->paginate(12);

        $metaTitle = "Cartelera de Eventos del Folklore Argentino: Festivales y Shows";
        $metaDescription = "Consulta la cartelera de eventos del folklore argentino. Encuentra información sobre festivales, conciertos y shows en todo el país. ¡Mantente al día con nuestra agenda!";
        return view('frontend.shows.index', compact('shows', 'metaTitle', 'metaDescription'));
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
        $show = Show::where('slug', $slug)->firstOrFail();
        $ultimos_shows = Show::where('estado', 1)
            ->where('id', '<>', $show->id)
            ->orderByDesc('created_at')
            ->take(10)
            ->get();
        $metaTitle = "Mi Folklore Argentino";
        $metaDescription = "El portal del folklore";
        return view('frontend.shows.show', compact('show', 'ultimos_shows', 'metaTitle', 'metaDescription'));
    }
}
