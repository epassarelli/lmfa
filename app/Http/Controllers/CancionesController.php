<?php

namespace App\Http\Controllers;

use App\Models\Cancion;
use App\Models\Interprete;
use Illuminate\Support\Str;
use Illuminate\Http\Request;

class CancionesController extends Controller
{
    public function index()
    {

        // Obtener las noticias en estado = 1 y ordenadas por el campo "publicar" desc
        $cancion = new Cancion();
        // Obtener los últimos 5 intérpretes
        $ultimas = $cancion->getNLast(Cancion::class, 12);
        $visitadas = $cancion->getNMostVisited(Cancion::class, 12);


        $metaTitle = "Letras de canciones del Folklore Argentino";
        $metaDescription = "Todas las letras de canciones del Folklore Argentino para cantar, cancionero folklórico";
        return view('canciones.index', compact('ultimas', 'visitadas', 'metaTitle', 'metaDescription'));
    }

    public function byArtista($slug)
    {
        // dd($slug);
        $interprete = Interprete::where('slug', $slug)->first();
        $canciones = $interprete->canciones()->where('estado', 1)->orderBy('cancion', 'asc')->get();
        $interpretes = Interprete::getInterpretesExcluding($interprete->id);
        $section = 'canciones';

        $metaTitle = "Letras de canciones de " . $interprete->interprete;
        $metaDescription = "Letras de canciones de " . $interprete->interprete . ", interprete del folklore argentino";
        return view('canciones.byArtista', compact('canciones', 'interprete', 'interpretes', 'section', 'metaTitle', 'metaDescription'));
    }

    public function show($slugInterprete, $slugCancion)
    {

        $interprete = Interprete::where('slug', $slugInterprete)->first();
        $cancion = Cancion::where('slug', $slugCancion)->firstOrFail();

        // $relacionadas = Cancion::where('estado', 1)
        //     ->where('interprete_id', $interprete->id)
        //     ->where('id', '<>', $cancion->id)
        //     ->orderBy('cancion', 'asc')
        //     ->take(12)
        //     ->get();
        // // dd($interprete);
        $related = $interprete->getRelatedContent($interprete, 'canciones', $cancion);

        $metaTitle = "Letra de " . $cancion->cancion . ", " . $interprete->interprete;
        // Decodifica las entidades HTML, elimina etiquetas HTML y toma los primeros 150 caracteres
        $metaDescription = Str::limit(strip_tags(html_entity_decode($cancion->letra)), 150);
        // Elimina los saltos de línea
        $metaDescription = preg_replace('/\r?\n|\r/', ' ', $metaDescription);
        return view('canciones.show', compact('cancion', 'interprete', 'related', 'metaTitle', 'metaDescription'));
    }
}
