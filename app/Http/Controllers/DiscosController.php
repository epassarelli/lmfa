<?php

namespace App\Http\Controllers;

use App\Models\Album;
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
}
