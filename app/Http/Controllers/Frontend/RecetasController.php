<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Comida;
use App\Services\LinkService;
use Illuminate\Support\Str;

class RecetasController extends Controller
{
    protected $linkService;

    public function __construct(LinkService $linkService)
    {
        $this->linkService = $linkService;
    }

    public function index()
    {
        $comida = new Comida();
        $ultimas = $comida->getNLast(Comida::class, 12);
        $visitadas = $comida->getNMostVisited(Comida::class, 12);
        $alphabet = range('a', 'z');

        $metaTitle = 'Recetas de Comidas Tipicas del Folklore Argentino: Sabores Tradicionales';
        $metaDescription = 'Descubre las recetas de comidas tipicas del folklore argentino. Aprende a preparar platos tradicionales con nuestras instrucciones faciles de seguir.';

        $breadcrumbs = [
            ['label' => 'Comidas', 'url' => route('comidas.index')]
        ];

        return view('frontend.recetas.index', compact('ultimas', 'visitadas', 'metaTitle', 'metaDescription', 'breadcrumbs', 'alphabet'));
    }

    public function show($slug)
    {
        $receta = Comida::where('slug', $slug)->with('images')->firstOrFail();

        $relacionadas = Comida::where('estado', 1)
            ->where('id', '<>', $receta->id)
            ->where('titulo', 'LIKE', substr($receta->titulo, 0, 1) . '%')
            ->take(6)
            ->get();

        if ($relacionadas->isEmpty()) {
            $relacionadas = Comida::where('estado', 1)
                ->where('id', '<>', $receta->id)
                ->latest()
                ->take(6)
                ->get();
        }

        $receta->increment('visitas');

        $metaTitle = 'Receta de ' . $receta->titulo . ' | Comida Tipica del Folklore';
        $metaDescription = Str::limit(strip_tags(html_entity_decode($receta->receta)), 150);
        $receta->receta = $this->linkService->autoLinkArtists($receta->receta);

        $breadcrumbs = [
            ['label' => 'Comidas', 'url' => route('comidas.index')],
            ['label' => $receta->titulo]
        ];

        return view('frontend.recetas.show', compact('receta', 'relacionadas', 'metaTitle', 'metaDescription', 'breadcrumbs'));
    }

    public function letra($letra)
    {
        $letra = Str::lower($letra);
        $comida = new Comida();
        $ultimas = $comida->getNLast(Comida::class, 12);
        $visitadas = $comida->getNMostVisited(Comida::class, 12);
        $comidas = Comida::where('titulo', 'LIKE', $letra . '%')->get();
        $alphabet = range('a', 'z');

        $metaTitle = "Recetas de comidas tipicas de Argentina que comienzan con {$letra}";
        $metaDescription = "Descubri recetas de comidas tipicas del folklore argentino que comienzan con la letra {$letra}.";
        $breadcrumbs = [
            ['label' => 'Comidas', 'url' => route('comidas.index')],
            ['label' => 'Letra ' . strtoupper($letra)]
        ];

        return view('frontend.recetas.letra', compact('ultimas', 'visitadas', 'comidas', 'letra', 'metaTitle', 'metaDescription', 'breadcrumbs', 'alphabet'));
    }
}
