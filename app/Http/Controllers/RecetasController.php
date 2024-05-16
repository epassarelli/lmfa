<?php

namespace App\Http\Controllers;

use App\Models\Comida;
use Illuminate\Http\Request;

class RecetasController extends Controller
{
    public function index()
    {
        // Obtener las noticias en estado = 1 y ordenadas por el campo "publicar" desc
        $recetas = Comida::where('estado', 1)
            ->orderBy('publicar', 'desc')
            ->paginate(12);
        $metaTitle = "Mi Folklore Argentino";
        $metaDescription = "El portal del folklore";
        return view('recetas.index', compact('recetas', 'metaTitle', 'metaDescription'));
    }

    public function show($slug)
    {
        $receta = Comida::where('slug', $slug)->firstOrFail();
        $ultimas_recetas = Comida::where('estado', 1)
            ->where('id', '<>', $receta->id)
            ->orderByDesc('created_at')
            ->take(10)
            ->get();
        $metaTitle = "Mi Folklore Argentino";
        $metaDescription = "El portal del folklore";
        return view('recetas.show', compact('receta', 'ultimas_recetas', 'metaTitle', 'metaDescription'));
    }
}
