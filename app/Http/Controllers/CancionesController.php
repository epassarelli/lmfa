<?php

namespace App\Http\Controllers;

use App\Models\Cancion;
use Illuminate\Http\Request;

class CancionesController extends Controller
{
    public function index()
    {
        // Obtener las noticias en estado = 1 y ordenadas por el campo "publicar" desc
        $canciones = Cancion::where('estado', 1)
            ->orderBy('publicar', 'desc')
            ->paginate(12);

        //dd($canciones);
        return view('canciones.index', compact('canciones'));
    }
}
