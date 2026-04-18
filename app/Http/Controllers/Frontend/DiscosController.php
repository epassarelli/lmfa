<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Album;
use App\Models\Interprete;
use Illuminate\Http\Request;

class DiscosController extends Controller
{
    public function index()
    {
        $discos = Album::where('estado', 1)->orderBy('created_at', 'desc')->paginate(24);

        $metaTitle = "Discografías de Folklore Argentino: Álbumes y Obras Destacadas";
        $metaDescription = "Explora las discografías completas del folklore argentino. Encuentra álbumes y canciones clásicas de artistas destacados. ¡Descubre la música tradicional de Argentina aquí!";

        $breadcrumbs = [
            ['label' => 'Discos', 'url' => route('discografias.index')]
        ];

        return view('frontend.discos.index', compact('discos', 'metaTitle', 'metaDescription', 'breadcrumbs'));
    }

    public function byArtista($slug)
    {
        $interprete = Interprete::where('slug', $slug)->first();
        $discos = $interprete->discos()->where('estado', 1)->with('images')->orderby('anio', 'desc')->get();
        $interpretes = Interprete::getInterpretesExcluding($interprete->id);
        $section = 'discografias';

        $metaTitle = "Discografía de " . $interprete->interprete;
        $metaDescription = "Discografía completa de {$interprete->interprete}, figura destacada del folklore argentino. Conocé sus álbumes, canciones y trayectoria musical.";

        $breadcrumbs = [
            ['label' => 'Artistas', 'url' => route('interpretes.index')],
            ['label' => $interprete->interprete, 'url' => route('artista.show', $interprete->slug)],
            ['label' => 'Discos']
        ];

        return view('frontend.discos.byArtista', compact('discos', 'interprete', 'interpretes', 'section', 'metaTitle', 'metaDescription', 'breadcrumbs'));
    }

    public function show($slugInterprete, $slugDisco)
    {
        $interprete = Interprete::where('slug', $slugInterprete)->first();
        $disco = Album::where('slug', $slugDisco)->with('images')->firstOrFail();

        // Incrementar el contador de visitas
        $disco->increment('visitas');

        $interpretes = Interprete::getInterpretesExcluding($interprete->id);

        $related = $interprete->getRelatedContent($interprete, 'discos', $disco, 'anio', 'desc');
        // dd($disco);

        $metaTitle = $disco->album . " (" . $disco->anio . ") - Disco de " . $interprete->interprete . " | Folklore Argentino";
        $metaDescription = $disco->album . " (" . $disco->anio . ") - Disco de " . $interprete->interprete . ". Escuchá y descubrí este álbum emblemático del folklore argentino con sus canciones, historia y más.";
        
        $breadcrumbs = [
            ['label' => 'Artistas', 'url' => route('interpretes.index')],
            ['label' => $interprete->interprete, 'url' => route('artista.show', $interprete->slug)],
            ['label' => 'Discos', 'url' => route('artista.discografia', $interprete->slug)],
            ['label' => $disco->album]
        ];

        return view('frontend.discos.show', compact('disco', 'interprete', 'interpretes', 'related', 'metaTitle', 'metaDescription', 'breadcrumbs'));
    }
}
