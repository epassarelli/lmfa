<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
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
        return view('frontend.radios.index', compact('radios'));
    }
}
