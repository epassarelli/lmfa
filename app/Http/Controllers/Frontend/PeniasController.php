<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Penia;
use Illuminate\Http\Request;

class PeniasController extends Controller
{
    public function index()
    {
        // Obtener las noticias en estado = 1 y ordenadas por el campo "publicar" desc
        $penias = Penia::where('estado', 1)
            ->with('images')
            ->orderBy('publicar', 'desc')
            ->paginate(12);
        
        $metaTitle = "Peñas Folkloricas de Argentina: Espacios de Encuentro y Tradición";
        $metaDescription = "Descubrí las peñas y centros culturales donde se vive el folklore en Argentina. Lugares para cantar, bailar y disfrutar de nuestra cultura.";

        return view('frontend.penias.index', compact('penias', 'metaTitle', 'metaDescription'));
    }
}
