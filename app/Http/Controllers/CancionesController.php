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
        $canciones = Cancion::where('estado', 1)
            ->orderBy('publicar', 'desc')
            ->paginate(12);


        $metaTitle = "Letras de canciones del Folklore Argentino";
        $metaDescription = "Todas las letras de canciones del Folklore Argentino para cantar, cancionero folklórico";
        return view('canciones.index', compact('canciones', 'metaTitle', 'metaDescription'));
    }

    public function byArtista($slug)
    {
        // dd($slug);
        $interprete = Interprete::where('slug', $slug)->first();
        $canciones = $interprete->canciones()->where('estado', 1)->paginate(12);

        $metaTitle = "Letras de canciones de " . $interprete->interprete;
        $metaDescription = "Todas las letras de canciones de " . $interprete->interprete;
        return view('canciones.byArtista', compact('canciones', 'interprete', 'metaTitle', 'metaDescription'));
    }

    public function show($slugInterprete, $slugCancion)
    {

        $interprete = Interprete::where('slug', $slugInterprete)->first();
        $cancion = Cancion::where('slug', $slugCancion)->firstOrFail();

        $relacionadas = Cancion::where('estado', 1)
            ->where('id', '<>', $cancion->id)
            ->orderByDesc('created_at')
            ->take(12)
            ->get();
        // dd($interprete);

        $metaTitle = "Letra de " . $cancion->cancion . ", " . $interprete->interprete;

        // Decodifica las entidades HTML, elimina etiquetas HTML y toma los primeros 150 caracteres
        $metaDescription = Str::limit(strip_tags(html_entity_decode($cancion->letra)), 150);

        // Elimina los saltos de línea
        $metaDescription = preg_replace('/\r?\n|\r/', ' ', $metaDescription);
        return view('canciones.show', compact('cancion', 'interprete', 'relacionadas', 'metaTitle', 'metaDescription'));
    }
}
