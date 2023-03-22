<?php

namespace App\Http\Controllers;

use App\Models\Album;
use App\Models\Interprete;
use Illuminate\Http\Request;

class DiscosController extends Controller
{
    public function index()
    {
        // Obtener las noticias en estado = 1 y ordenadas por el campo "publicar" desc
        $discos = Album::where('estado', 1)
            ->orderBy('publicar', 'desc')
            ->paginate(12);
        return view('discos.index', compact('discos'));
    }

    public function byArtista($slug)
    {
        // dd($slug);
        $interprete = Interprete::where('slug', $slug)->first();
        $discos = $interprete->discos()->where('estado', 1)->paginate(12);
        // $discos = Show::where('estado', 1)
        //     ->where('estado', 1)
        //     ->orderBy('publicar', 'desc')
        //     ->paginate(12);
        return view('discos.byArtista', compact('discos', 'interprete'));
    }

    public function show($slugInterprete, $slugDisco)
    {
        // dd($slugInterprete, $slugDisco);
        $interprete = Interprete::where('slug', $slugInterprete)->first();
        $disco = Album::where('slug', $slugDisco)->firstOrFail();

        $relacionados = Album::where('estado', 1)
            ->where('id', '<>', $disco->id)
            ->orderByDesc('created_at')
            ->take(10)
            ->get();
        // dd($interprete, $disco, $relacionados);
        return view('discos.show', compact('disco', 'interprete', 'relacionados'));
    }
}
