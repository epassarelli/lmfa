<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Http\Livewire\Backend\Interpretes;
use App\Models\Album;
use App\Models\Cancion;
use App\Models\Categoria;
use App\Models\Event;
use App\Models\Interprete;
use App\Models\News;
use App\Support\SeoMetadata;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        $categorias = Categoria::get();

        $ultimasNoticias = News::publishedVisible()
            ->with(['categoria', 'images'])
            ->latest()
            ->take(50)
            ->get();

        $actualidad = News::publishedVisible()
            ->where('categoria_id', 1)
            ->with(['categoria', 'interpretes', 'images'])
            ->latest()
            ->take(6)
            ->get();

        $festivales = News::publishedVisible()
            ->where('categoria_id', 2)
            ->with(['categoria', 'images'])
            ->latest()
            ->take(6)
            ->get();

        $lanzamientos = News::publishedVisible()
            ->where('categoria_id', 3)
            ->with(['categoria', 'images'])
            ->latest()
            ->take(6)
            ->get();

        $entrevistas = News::publishedVisible()
            ->where('categoria_id', 4)
            ->with(['categoria', 'images'])
            ->latest()
            ->take(6)
            ->get();

        $cartelera = News::publishedVisible()
            ->where('categoria_id', 5)
            ->with(['categoria', 'images'])
            ->latest()
            ->take(6)
            ->get();

        $shows = Event::publishedVisible()
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

        $seo = SeoMetadata::home();
        $metaTitle = $seo['title'];
        $metaDescription = $seo['description'];
        $h1 = $seo['h1'];

        return view('frontend.home', compact(
            'metaTitle',
            'metaDescription',
            'h1',
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
