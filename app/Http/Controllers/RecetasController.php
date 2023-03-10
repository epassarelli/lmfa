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
        return view('recetas.index', compact('recetas'));
    }
}
