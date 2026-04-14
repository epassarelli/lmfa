<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

use App\Models\Mito;
use App\Services\LinkService;

class MitosController extends Controller
{
    protected $linkService;

    public function __construct(LinkService $linkService)
    {
        $this->linkService = $linkService;
    }
    public function index()
    {
        // Obtener las noticias en estado = 1 y ordenadas por el campo "publicar" desc
        $mito = new Mito();
        // Obtener los últimos 5 intérpretes
        $ultimos = $mito->getNLast(Mito::class, 12);
        $visitados = $mito->getNMostVisited(Mito::class, 12);

        $metaTitle = "Mitos y Leyendas del Folklore Argentino: Historias y Tradiciones";
        $metaDescription = "Explora los mitos y leyendas más fascinantes del folklore argentino. Conoce historias y tradiciones que han pasado de generación en generación. ¡Visítanos para saber más!";
        $breadcrumbs = [
            ['label' => 'Mitos', 'url' => route('mitos.index')]
        ];

        return view('frontend.mitos.index', compact('ultimos', 'visitados', 'metaTitle', 'metaDescription', 'breadcrumbs'));
    }

    public function show($slug)
    {
        $mito = Mito::where('slug', $slug)->with('images')->firstOrFail();
        
        // Relacionados: misma letra inicial (excluyendo el actual)
        $relacionados = Mito::where('estado', 1)
            ->where('id', '<>', $mito->id)
            ->where('titulo', 'LIKE', substr($mito->titulo, 0, 1) . '%')
            ->take(6)
            ->get();

        if ($relacionados->isEmpty()) {
            $relacionados = Mito::where('estado', 1)
                ->where('id', '<>', $mito->id)
                ->latest()
                ->take(6)
                ->get();
        }

        // Incrementar el contador de visitas
        $mito->increment('visitas');

        $metaTitle = $mito->titulo . " | Mitos y leyendas urbanas";
        $metaDescription = Str::limit(strip_tags(html_entity_decode($mito->mito)), 150);

        $mito->mito = $this->linkService->autoLinkArtists($mito->mito);

        $breadcrumbs = [
            ['label' => 'Mitos', 'url' => route('mitos.index')],
            ['label' => $mito->titulo]
        ];

        return view('frontend.mitos.show', compact('mito', 'relacionados', 'metaTitle', 'metaDescription', 'breadcrumbs'));
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
