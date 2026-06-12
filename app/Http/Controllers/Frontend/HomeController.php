<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Http\Livewire\Backend\Interpretes;
use Illuminate\Http\Request;

use App\Models\Interprete;
use App\Models\News;
use App\Models\Show;
use App\Models\Album;
use App\Models\Cancion;
use App\Models\Categoria;

class HomeController extends Controller
{
    public function index()
    {
        $categorias = Categoria::get();

        $ultimasNoticias = News::where('editorial_status', 'published')
            ->with(['categoria', 'images'])
            ->latest()
            ->take(50)
            ->get();

        $actualidad = News::where('editorial_status', 'published')
            ->where('categoria_id', 1)
            ->with(['categoria', 'interpretes', 'images'])
            ->latest()
            ->take(6)
            ->get();

        $festivales = News::where('editorial_status', 'published')
            ->where('categoria_id', 2)
            ->with(['categoria', 'images'])
            ->latest()
            ->take(6)
            ->get();

        $lanzamientos = News::where('editorial_status', 'published')
            ->where('categoria_id', 3)
            ->with(['categoria', 'images'])
            ->latest()
            ->take(6)
            ->get();

        $entrevistas = News::where('editorial_status', 'published')
            ->where('categoria_id', 4)
            ->with(['categoria', 'images'])
            ->latest()
            ->take(6)
            ->get();

        $cartelera = News::where('editorial_status', 'published')
            ->where('categoria_id', 5)
            ->with(['categoria', 'images'])
            ->latest()
            ->take(6)
            ->get();

        $shows = Show::where('editorial_status', 'published')
            ->where('start_at', '>=', now())
            ->with(['interpretes', 'images'])
            ->orderBy('start_at', 'asc')
            ->paginate(4);

        $interprete = new Interprete();
        $ultimosArtistas = $interprete->getNLast(Interprete::class, 5);

        $disco = new Album();
        $ultimosDiscos = $disco->getNLast(Album::class, 4);

        $cancion = new Cancion();
        $canciones = $cancion->getNLast(Cancion::class, 6);

        $metaTitle = "Mi Folklore Argentino | Nuestras Tradiciones y Costumbres";
        $metaDescription = "Bienvenido a Mi Folklore Argentino, tu portal sobre la cultura y tradiciones de Argentina. Descubre musica, danzas y mas. Visitanos hoy!";

        return view('frontend.home', compact(
            'metaTitle',
            'metaDescription',
            'ultimasNoticias',
            'ultimosArtistas',
            'shows',
            'ultimosDiscos',
            'canciones',
            'actualidad',
            'festivales',
            'lanzamientos',
            'entrevistas',
            'cartelera',
            'categorias'
        ));
    }
}
