<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Http\Livewire\Backend\Interpretes;
use Illuminate\Http\Request;

use App\Models\Interprete;
use App\Models\Noticia;
use App\Models\Show;
use App\Models\Disco;
use App\Models\Cancion;

class HomeController extends Controller
{
    public function index()
    {
        $noticia = new Noticia();
        // Obtener los últimos 5 intérpretes
        $noticiasDestacadas = $noticia->getNLast(Noticia::class, 4);

        $interprete = new Interprete();
        // Obtener los últimos 5 intérpretes
        $artistas = $interprete->getNLast(Interprete::class, 6);

        $metaTitle = "Mi Folklore Argentino | Todo sobre Nuestras Tradiciones y Costumbres";
        $metaDescription = "Bienvenido a Mi Folklore Argentino, tu portal sobre la cultura y tradiciones de Argentina. Descubre música, danzas y más. ¡Visítanos hoy!";
        return view('frontend.home', compact('metaTitle', 'metaDescription', 'noticiasDestacadas', 'artistas'));
    }
}
