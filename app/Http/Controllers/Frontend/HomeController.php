<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Http\Livewire\Backend\Interpretes;
use Illuminate\Http\Request;

use App\Models\Interprete;
use App\Models\Noticia;
use App\Models\Show;
use App\Models\Album;
use App\Models\Cancion;

class HomeController extends Controller
{
    public function index()
    {
        // Obtener las últimas 4 noticias
        $noticia = new Noticia();
        $noticias = $noticia->getNLast(Noticia::class, 4);

        // Obtener las últimas 4 shows
        $show = new Show();
        // $shows = $show->getNLast(Show::class, 4);
        $shows = Show::where('estado', 1)
            ->where('fecha', '>=', now())
            ->orderBy('fecha', 'asc')
            ->paginate(4);

        // Obtener los últimos 3 intérpretes
        $interprete = new Interprete();
        $interpretes = $interprete->getNLast(Interprete::class, 4);

        // Obtener los últimos 3 intérpretes
        $disco = new Album();
        $discos = $disco->getNLast(Album::class, 4);

        // Obtener los últimos 3 intérpretes
        $cancion = new Cancion();
        $canciones = $cancion->getNLast(Cancion::class, 6);

        $metaTitle = "Mi Folklore Argentino | Todo sobre Nuestras Tradiciones y Costumbres";
        $metaDescription = "Bienvenido a Mi Folklore Argentino, tu portal sobre la cultura y tradiciones de Argentina. Descubre música, danzas y más. ¡Visítanos hoy!";

        return view('frontend.home', compact('metaTitle', 'metaDescription', 'noticias', 'interpretes', 'shows', 'discos', 'canciones'));
    }
}
