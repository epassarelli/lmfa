<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Mito;
use App\Services\LinkService;
use Illuminate\Support\Str;

class MitosController extends Controller
{
    protected $linkService;

    public function __construct(LinkService $linkService)
    {
        $this->linkService = $linkService;
    }

    public function index()
    {
        $mito = new Mito();
        $ultimos = $mito->getNLast(Mito::class, 12);
        $visitados = $mito->getNMostVisited(Mito::class, 12);
        $alphabet = range('a', 'z');

        $metaTitle = 'Mitos y Leyendas del Folklore Argentino: Historias y Tradiciones';
        $metaDescription = 'Explora los mitos y leyendas mas fascinantes del folklore argentino. Conoce historias y tradiciones que han pasado de generacion en generacion.';
        $breadcrumbs = [
            ['label' => 'Mitos', 'url' => route('mitos.index')]
        ];

        return view('frontend.mitos.index', compact('ultimos', 'visitados', 'metaTitle', 'metaDescription', 'breadcrumbs', 'alphabet'));
    }

    public function show($slug)
    {
        $mito = Mito::where('slug', $slug)->with('images')->firstOrFail();

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

        $mito->increment('visitas');

        $metaTitle = $mito->titulo . ' | Mitos y leyendas urbanas';
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
        $letra = Str::lower($letra);
        $mito = new Mito();
        $ultimos = $mito->getNLast(Mito::class, 12);
        $visitados = $mito->getNMostVisited(Mito::class, 12);
        $mitos = Mito::where('titulo', 'LIKE', $letra . '%')->get();
        $alphabet = range('a', 'z');

        $metaTitle = "Mitos y leyendas urbanas argentinas que comienzan con {$letra}";
        $metaDescription = "Mitos, leyendas y fabulas del folklore argentino que comienzan con la letra {$letra}.";
        $breadcrumbs = [
            ['label' => 'Mitos', 'url' => route('mitos.index')],
            ['label' => 'Letra ' . strtoupper($letra)]
        ];

        return view('frontend.mitos.letra', compact('ultimos', 'visitados', 'mitos', 'letra', 'metaTitle', 'metaDescription', 'breadcrumbs', 'alphabet'));
    }
}
