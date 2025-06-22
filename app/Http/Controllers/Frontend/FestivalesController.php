<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

use App\Models\Festival;

class FestivalesController extends Controller
{
    public function index()
    {

        // Obtener las noticias en estado = 1 y ordenadas por el campo "publicar" desc
        $festival = new Festival();
        // Obtener los últimos 5 intérpretes
        $ultimos = $festival->getNLast(Festival::class, 12);
        $visitados = $festival->getNMostVisited(Festival::class, 20);

        $metaTitle = "Festivales y Fiestas del Folklore Argentino: Tradición y Cultura";
        $metaDescription = "Descubre los festivales y fiestas tradicionales del folklore argentino. Mantente informado sobre los eventos culturales más importantes de Argentina. ¡Explora nuestras guías de festivales ahora!";

        return view('frontend.festivales.index', compact('ultimos', 'visitados', 'metaTitle', 'metaDescription'));
    }

    public function show($slug)
    {
        $festival = Festival::where('slug', $slug)->firstOrFail();

        // Relciondos x prov
        // $relted = Festival::get;

        $ultimos_festivales = Festival::where('estado', 1)
            ->where('id', '<>', $festival->id)
            ->orderByDesc('created_at')
            ->take(10)
            ->get();
        // Incrementar el contador de visitas
        $festival->increment('visitas');

        $metaTitle = $festival->titulo . " - Folklore Argentino";
        $metaDescription = Str::limit(strip_tags(html_entity_decode($festival->detalle)), 150);
        return view('frontend.festivales.show', compact('festival', 'ultimos_festivales', 'metaTitle', 'metaDescription'));
    }
}
