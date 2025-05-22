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
use App\Models\Categoria;

class HomeController extends Controller
{
    public function index()
    {
        // Obtener las últimas 4 noticias
        // $noticia = new Noticia();
        // $noticias = $noticia->getNLast(Noticia::class, 10)->toArray();

        $categorias = Categoria::get();

        $ultimasNoticias = Noticia::where('estado', 1)
            ->with(['categoria']) // Carga relaciones
            ->latest()
            ->take(20)
            ->get();


        $actualidad = Noticia::where('estado', 1)
            ->where('categoria_id', 1) // Filtrar por categoría específica
            ->with(['categoria', 'interpretes'])
            ->latest()
            ->take(6)
            ->get();
        // ->toArray();

        $festivales = Noticia::where('estado', 1)
            ->where('categoria_id', 2) // Filtrar por categoría específica
            ->with(['categoria'])
            ->latest()
            ->take(6)
            ->get();
        // ->toArray();

        $lanzamientos = Noticia::where('estado', 1)
            ->where('categoria_id', 3) // Filtrar por categoría específica
            ->with(['categoria'])
            ->latest()
            ->take(6)
            ->get();
        // ->toArray();

        $entrevistas = Noticia::where('estado', 1)
            ->where('categoria_id', 4) // Filtrar por categoría específica
            ->with(['categoria'])
            ->latest()
            ->take(6)
            ->get();
        // ->toArray();

        $cartelera = Noticia::where('estado', 1)
            ->where('categoria_id', 5) // Filtrar por categoría específica
            ->with(['categoria'])
            ->latest()
            ->take(6)
            ->get();
        // ->toArray();

        // Obtener las últimas 4 shows
        $show = new Show();
        // $shows = $show->getNLast(Show::class, 4);
        $shows = Show::where('estado', 1)
            ->where('fecha', '>=', now())
            ->orderBy('fecha', 'asc')
            ->paginate(4);

        // Obtener los últimos 3 intérpretes
        $interprete = new Interprete();
        $interpretes = $interprete->getNLast(Interprete::class, 5);

        // Obtener los últimos 3 intérpretes
        $disco = new Album();
        $discos = $disco->getNLast(Album::class, 4);

        // Obtener los últimos 3 intérpretes
        $cancion = new Cancion();
        $canciones = $cancion->getNLast(Cancion::class, 6);

        $metaTitle = "Mi Folklore Argentino | Nuestras Tradiciones y Costumbres";
        $metaDescription = "Bienvenido a Mi Folklore Argentino, tu portal sobre la cultura y tradiciones de Argentina. Descubre música, danzas y más. ¡Visítanos hoy!";

        return view('frontend.home', compact('metaTitle', 'metaDescription', 'ultimasNoticias', 'interpretes', 'shows', 'discos', 'canciones', 'actualidad', 'festivales', 'lanzamientos', 'entrevistas', 'cartelera', 'categorias'));
    }
}
