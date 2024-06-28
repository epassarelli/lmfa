<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Comida;
use Illuminate\Http\Request;

class RecetasController extends Controller
{
    public function index()
    {
        $comida = new Comida();
        // Obtener los últimos 5 intérpretes
        $ultimas = $comida->getNLast(Comida::class, 12);
        $visitadas = $comida->getNMostVisited(Comida::class, 12);

        // Obtener las noticias en estado = 1 y ordenadas por el campo "publicar" desc
        // $recetas = Comida::where('estado', 1)
        //     ->orderBy('publicar', 'desc')
        //     ->paginate(12);

        $metaTitle = "Recetas de comidas típicas del folklore argentino";
        $metaDescription = "Recetas de comidas típicas del folklore argentino";

        return view('frontend.recetas.index', compact('ultimas', 'visitadas', 'metaTitle', 'metaDescription'));
    }

    public function show($slug)
    {
        $receta = Comida::where('slug', $slug)->firstOrFail();
        $ultimas_recetas = Comida::where('estado', 1)
            ->where('id', '<>', $receta->id)
            ->orderByDesc('created_at')
            ->take(10)
            ->get();

        // Incrementar el contador de visitas
        $receta->increment('visitas');

        $metaTitle = "Mi Folklore Argentino";
        $metaDescription = "El portal del folklore";
        return view('frontend.recetas.show', compact('receta', 'ultimas_recetas', 'metaTitle', 'metaDescription'));
    }

    public function letra($letra)
    {
        $comida = new Comida();
        // Obtener los últimos 5 intérpretes
        $ultimas = $comida->getNLast(Comida::class, 12);
        $visitadas = $comida->getNMostVisited(Comida::class, 12);


        // Lógica para obtener intérpretes cuya letra del título comience con $letra
        $comidas = Comida::where('titulo', 'LIKE', $letra . '%')->get();

        $metaTitle = "Biografías de Interpretes folkloricos de Argentina que comienzan con $letra";
        $metaDescription = "Biografías de Interpretes folkloricos de Argentina que comienzan con $letra";

        return view('frontend.recetas.letra', compact('ultimas', 'visitadas', 'comidas', 'letra', 'metaTitle', 'metaDescription'));
    }
}
