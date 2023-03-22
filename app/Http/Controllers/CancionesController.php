<?php

namespace App\Http\Controllers;

use App\Models\Cancion;
use App\Models\Interprete;
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

    public function byArtista($slug)
    {
        // dd($slug);
        $interprete = Interprete::where('slug', $slug)->first();
        $canciones = $interprete->canciones()->where('estado', 1)->paginate(12);
        // $canciones = Show::where('estado', 1)
        //     ->where('estado', 1)
        //     ->orderBy('publicar', 'desc')
        //     ->paginate(12);
        return view('canciones.byArtista', compact('canciones', 'interprete'));
    }

    public function show($slugInterprete, $slugCancion)
    {
        // dd($slugInterprete, $slugCancion);
        $interprete = Interprete::where('slug', $slugInterprete)->first();
        $cancion = Cancion::where('slug', $slugCancion)->firstOrFail();

        $relacionadas = Cancion::where('estado', 1)
            ->where('id', '<>', $cancion->id)
            ->orderByDesc('created_at')
            ->take(12)
            ->get();
        // dd($interprete, $cancion, $relacionados);
        return view('canciones.show', compact('cancion', 'interprete', 'relacionadas'));
    }
}
