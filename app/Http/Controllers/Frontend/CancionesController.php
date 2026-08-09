<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Cancion;
use App\Models\Interprete;
use App\Services\LinkService;
use App\Support\SeoMetadata;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class CancionesController extends Controller
{
    protected $linkService;

    public function __construct(LinkService $linkService)
    {
        $this->linkService = $linkService;
    }

    public function index()
    {
        $canciones = Cancion::query()
            ->where('estado', 1)
            ->with([
                'interprete:id,interprete,slug,foto',
                'albunes:id,album,slug,interprete_id',
                'albunes.interprete:id,interprete,slug',
            ])
            ->orderBy('cancion', 'asc')
            ->simplePaginate(36);

        $metaTitle = 'Letras de Canciones del Folklore Argentino | Cancionero Popular';
        $metaDescription = 'Encuentra letras de canciones del folklore argentino y explora un cancionero popular pensado para consulta y descubrimiento.';

        $breadcrumbs = [
            ['label' => 'Cancionero', 'url' => route('canciones.index')],
        ];

        return view('frontend.canciones.index', compact('canciones', 'metaTitle', 'metaDescription', 'breadcrumbs'));
    }

    public function letra($letra)
    {
        $canciones = Cancion::query()
            ->where('estado', 1)
            ->with([
                'interprete:id,interprete,slug,foto',
                'albunes:id,album,slug,interprete_id',
                'albunes.interprete:id,interprete,slug',
            ])
            ->where('cancion', 'LIKE', $letra.'%')
            ->orderBy('cancion', 'asc')
            ->simplePaginate(36);

        $metaTitle = "Letras de Canciones folkloricas de Argentina que comienzan con {$letra}";
        $metaDescription = "Letras de Canciones folkloricas de Argentina que comienzan con {$letra}";

        $breadcrumbs = [
            ['label' => 'Cancionero', 'url' => route('canciones.index')],
            ['label' => 'Letra '.strtoupper($letra)],
        ];

        return view('frontend.canciones.letra', compact('canciones', 'letra', 'metaTitle', 'metaDescription', 'breadcrumbs'));
    }

    public function byArtista($slug)
    {
        $interprete = Interprete::where('slug', $slug)->first();
        $canciones = $interprete->canciones()
            ->where('estado', 1)
            ->with('albunes')
            ->orderBy('cancion', 'asc')
            ->get();

        $interpretes = Interprete::getInterpretesExcluding($interprete->id);
        $section = 'canciones';

        $metaTitle = 'Letras de canciones de '.$interprete->interprete;
        $metaDescription = 'Letras de canciones de '.$interprete->interprete.', referente del folklore argentino. Descubri su cancionero popular.';

        $breadcrumbs = [
            ['label' => 'Artistas', 'url' => route('interpretes.index')],
            ['label' => $interprete->interprete, 'url' => route('artista.show', $interprete->slug)],
            ['label' => 'Canciones'],
        ];

        return view('frontend.canciones.byArtista', compact('canciones', 'interprete', 'interpretes', 'section', 'metaTitle', 'metaDescription', 'breadcrumbs'));
    }

    public function show($slugInterprete, $slugCancion)
    {
        $interprete = Interprete::where('slug', $slugInterprete)->first();

        $cancion = Cancion::where('slug', $slugCancion)
            ->where('interprete_id', $interprete->id)
            ->firstOrFail();

        $cancion->increment('visitas');
        $interpretes = Interprete::getInterpretesExcluding($interprete->id);
        $related = $interprete->getRelatedContent($interprete, 'canciones', $cancion, 'cancion', 'asc');

        $seo = SeoMetadata::song($cancion, $interprete);
        $metaTitle = $seo['title'];
        $metaDescription = $seo['description'];
        $h1 = $seo['h1'];

        $breadcrumbs = [
            ['label' => 'Artistas', 'url' => route('interpretes.index')],
            ['label' => $interprete->interprete, 'url' => route('artista.show', $interprete->slug)],
            ['label' => 'Canciones', 'url' => route('artista.canciones', $interprete->slug)],
            ['label' => $cancion->cancion],
        ];

        $cancion->letra = $this->linkService->autoLinkArtists($cancion->letra);

        return view('frontend.canciones.show', compact('cancion', 'interprete', 'interpretes', 'related', 'metaTitle', 'metaDescription', 'h1', 'breadcrumbs'));
    }
}
