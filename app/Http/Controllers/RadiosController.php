<?php

namespace App\Http\Controllers;

use App\Models\Radio;
use Illuminate\Http\Request;

class RadiosController extends Controller
{
    public function index()
    {
        // Obtener las noticias en estado = 1 y ordenadas por el campo "publicar" desc
        $radios = Radio::where('estado', 1)
            ->orderBy('publicar', 'desc')
            ->paginate(12);
        return view('radios.index', compact('radios'));
    }
}
