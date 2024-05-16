<?php

namespace App\Http\Controllers;

use App\Models\Penia;
use Illuminate\Http\Request;

class PeniasController extends Controller
{
    public function index()
    {
        // Obtener las noticias en estado = 1 y ordenadas por el campo "publicar" desc
        $penias = Penia::where('estado', 1)
            ->orderBy('publicar', 'desc')
            ->paginate(12);
        return view('penias.index', compact('penias'));
    }
}
