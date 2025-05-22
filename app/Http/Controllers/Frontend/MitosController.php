<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

use App\Models\Mito;

class MitosController extends Controller
{
    public function index()
    {
        // Obtener las noticias en estado = 1 y ordenadas por el campo "publicar" desc
        $mito = new Mito();
        // Obtener los últimos 5 intérpretes
        $ultimos = $mito->getNLast(Mito::class, 12);
        $visitados = $mito->getNMostVisited(Mito::class, 12);

        $metaTitle = "Mitos y Leyendas del Folklore Argentino: Historias y Tradiciones";
        $metaDescription = "Explora los mitos y leyendas más fascinantes del folklore argentino. Conoce historias y tradiciones que han pasado de generación en generación. ¡Visítanos para saber más!";
        return view('frontend.mitos.index', compact('ultimos', 'visitados', 'metaTitle', 'metaDescription'));
    }

    public function show($slug)
    {
        $mito = Mito::where('slug', $slug)->firstOrFail();
        $ultimos_mitos = Mito::where('estado', 1)
            ->where('id', '<>', $mito->id)
            ->orderByDesc('created_at')
            ->take(10)
            ->get();
        // Incrementar el contador de visitas
        $mito->increment('visitas');

        $metaTitle = $mito->titulo . " | Mitos y leyendas urbanas";
        $metaDescription = Str::limit(strip_tags(html_entity_decode($mito->mito)), 150);;
        return view('frontend.mitos.show', compact('mito', 'ultimos_mitos', 'metaTitle', 'metaDescription'));
    }


    public function letra($letra)
    {
        $mito = new Mito();
        // Obtener los últimos 5 intérpretes
        $ultimos = $mito->getNLast(Mito::class, 12);
        $visitados = $mito->getNMostVisited(Mito::class, 12);


        // Lógica para obtener intérpretes cuya letra del título comience con $letra
        $mitos = Mito::where('titulo', 'LIKE', $letra . '%')->get();

        $metaTitle = "Mitos y leyendas urbanas argentinas que comienzan con $letra";
        $metaDescription = "Mitos, leyendas y fábulas del folklore argentino que comienzan con la letra {$letra}. Descubrí historias populares y creencias ancestrales.";


        return view('frontend.mitos.letra', compact('ultimos', 'visitados', 'mitos', 'letra', 'metaTitle', 'metaDescription'));
    }
}
