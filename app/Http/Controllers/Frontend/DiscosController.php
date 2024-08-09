<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Album;
use App\Models\Interprete;
use Illuminate\Http\Request;

class DiscosController extends Controller
{
    public function index()
    {
        // Obtener las noticias en estado = 1 y ordenadas por el campo "publicar" desc
        $disco = new Album();
        // Obtener los últimos 5 intérpretes
        $ultimos = $disco->getNLast(Album::class, 12);
        $visitados = $disco->getNMostVisited(Album::class, 12);


        $metaTitle = "Discografías de Folklore Argentino: Álbumes y Obras Destacadas";
        $metaDescription = "Explora las discografías completas del folklore argentino. Encuentra álbumes y canciones clásicas de artistas destacados. ¡Descubre la música tradicional de Argentina aquí!";

        return view('frontend.discos.index', compact('ultimos', 'visitados', 'metaTitle', 'metaDescription'));
    }

    public function byArtista($slug)
    {
        $interprete = Interprete::where('slug', $slug)->first();
        $discos = $interprete->discos()->where('estado', 1)->orderby('anio', 'desc')->get();
        $interpretes = Interprete::getInterpretesExcluding($interprete->id);
        $section = 'discografias';

        $metaTitle = "Discografía de " . $interprete->interprete;
        $metaDescription = "Discografía de " . $interprete->interprete . ", interprete del folklore argentino";
        return view('frontend.discos.byArtista', compact('discos', 'interprete', 'interpretes', 'section', 'metaTitle', 'metaDescription'));
    }

    public function show($slugInterprete, $slugDisco)
    {
        $interprete = Interprete::where('slug', $slugInterprete)->first();
        $disco = Album::where('slug', $slugDisco)->firstOrFail();
        // Incrementar el contador de visitas
        $disco->increment('visitas');
        $interpretes = Interprete::getInterpretesExcluding($interprete->id);

        $related = $interprete->getRelatedContent($interprete, 'discos', $disco, 'anio', 'desc');

        $metaTitle = $disco->titulo . " - Discografía de " . $interprete->interprete;
        $metaDescription = $disco->titulo . "Discografía de " . $interprete->interprete . ", interprete del folklore argentino";

        return view('frontend.discos.show', compact('disco', 'interprete', 'interpretes', 'related', 'metaTitle', 'metaDescription'));
    }
}
