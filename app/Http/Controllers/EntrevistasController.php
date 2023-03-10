<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;

class EntrevistasController extends Controller
{
    public function index()
    {
        // Obtener las noticias en estado = 1 y ordenadas por el campo "publicar" desc
        $entrevistas = Entrevista::where('estado', 1)
            ->orderBy('publicar', 'desc')
            ->paginate(12);
        return view('entrevistas.index', compact('entrevistas'));
    }
}
