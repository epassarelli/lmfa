<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Festival;
use Illuminate\Http\Request;

class FestivalesController extends Controller
{
    public function index()
    {

        // Obtener las noticias en estado = 1 y ordenadas por el campo "publicar" desc
        $festival = new Festival();
        // Obtener los últimos 5 intérpretes
        $ultimos = $festival->getNLast(Festival::class, 12);
        $visitados = $festival->getNMostVisited(Festival::class, 12);


        $metaTitle = "Festivales y Fiestas tradicionales";
        $metaDescription = "Festivales y Fiestas tradicionales del folklore argentino";
        return view('frontend.festivales.index', compact('ultimos', 'visitados', 'metaTitle', 'metaDescription'));
    }

    public function show($slug)
    {
        $festival = Festival::where('slug', $slug)->firstOrFail();
        $ultimos_festivales = Festival::where('estado', 1)
            ->where('id', '<>', $festival->id)
            ->orderByDesc('created_at')
            ->take(10)
            ->get();
        // Incrementar el contador de visitas
        $festival->increment('visitas');

        $metaTitle = $festival->titulo . " - Folklore Argentino";
        $metaDescription = "El portal del folklore";
        return view('frontend.festivales.show', compact('festival', 'ultimos_festivales', 'metaTitle', 'metaDescription'));
    }
}
