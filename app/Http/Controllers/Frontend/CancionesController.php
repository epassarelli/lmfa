<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Cancion;
use App\Models\Interprete;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Services\LinkService;

class CancionesController extends Controller
{
    protected $linkService;

    public function __construct(LinkService $linkService)
    {
        $this->linkService = $linkService;
    }
    public function index()
    {
        $canciones = Cancion::where('estado', 1)->orderBy('cancion', 'asc')->paginate(48);

        $metaTitle = "Letras de Canciones del Folklore Argentino | Cancionero Popular";
        $metaDescription = "Encuentra las letras de tus canciones favoritas del folklore argentino. Nuestro cancionero folklórico tiene todas las letras que necesitas para cantar. ¡Visítanos!";

        $breadcrumbs = [
            ['label' => 'Cancionero', 'url' => route('canciones.index')]
        ];

        return view('frontend.canciones.index', compact('canciones', 'metaTitle', 'metaDescription', 'breadcrumbs'));
    }

    public function letra($letra)
    {
        $canciones = Cancion::where('estado', 1)
            ->where('cancion', 'LIKE', $letra . '%')
            ->orderBy('cancion', 'asc')
            ->paginate(48);

        $metaTitle = "Letras de Canciones folklóricas de Argentina que comienzan con $letra";
        $metaDescription = "Letras de Canciones folklóricas de Argentina que comienzan con $letra";

        $breadcrumbs = [
            ['label' => 'Cancionero', 'url' => route('canciones.index')],
            ['label' => 'Letra ' . strtoupper($letra)]
        ];

        return view('frontend.canciones.letra', compact('canciones', 'letra', 'metaTitle', 'metaDescription', 'breadcrumbs'));
    }

    public function byArtista($slug)
    {
        // dd($slug);
        $interprete = Interprete::where('slug', $slug)->first();
        // $canciones = $interprete->canciones()->where('estado', 1)->orderBy('cancion', 'asc')->get();
        $canciones = $interprete->canciones()
            ->where('estado', 1)
            ->with('albunes') // Cargar los discos relacionados
            ->orderBy('cancion', 'asc')
            ->get();

        $interpretes = Interprete::getInterpretesExcluding($interprete->id);
        $section = 'canciones';

        $metaTitle = "Letras de canciones de " . $interprete->interprete;
        $metaDescription = "Letras de canciones de " . $interprete->interprete . ", referente del folklore argentino. Descubrí su cancionero popular y disfrutá su música.";

        $breadcrumbs = [
            ['label' => 'Artistas', 'url' => route('interpretes.index')],
            ['label' => $interprete->interprete, 'url' => route('artista.show', $interprete->slug)],
            ['label' => 'Canciones']
        ];

        return view('frontend.canciones.byArtista', compact('canciones', 'interprete', 'interpretes', 'section', 'metaTitle', 'metaDescription', 'breadcrumbs'));
    }

    public function show($slugInterprete, $slugCancion)
    {

        $interprete = Interprete::where('slug', $slugInterprete)->first();

        // Busca la canción por su slug y asegura que pertenezca al intérprete encontrado
        $cancion = Cancion::where('slug', $slugCancion)
            ->where('interprete_id', $interprete->id)
            ->firstOrFail();
        // $cancion = Cancion::where('slug', $slugCancion)->firstOrFail();

        // Incrementar el contador de visitas
        $cancion->increment('visitas');
        $interpretes = Interprete::getInterpretesExcluding($interprete->id);

        $related = $interprete->getRelatedContent($interprete, 'canciones', $cancion, 'cancion', 'asc');

        $metaTitle = "Letra de " . $cancion->cancion . ", " . $interprete->interprete;
        // Decodifica las entidades HTML, elimina etiquetas HTML y toma los primeros 150 caracteres

        // $metaDescription = Str::limit(strip_tags(html_entity_decode($cancion->letra)), 150);
        $letraContent = strip_tags(html_entity_decode($cancion->letra));

        if (strlen($letraContent) > 50) {
            $metaDescription = Str::limit($letraContent, 150);
        } else {
            $metaDescription = $interprete->interprete . ', ' . $cancion->cancion;
        }
        // Elimina los saltos de línea
        $metaDescription = preg_replace('/\r?\n|\r/', ' ', $metaDescription);

        $breadcrumbs = [
            ['label' => 'Artistas', 'url' => route('interpretes.index')],
            ['label' => $interprete->interprete, 'url' => route('artista.show', $interprete->slug)],
            ['label' => 'Canciones', 'url' => route('artista.canciones', $interprete->slug)],
            ['label' => $cancion->cancion]
        ];

        $cancion->letra = $this->linkService->autoLinkArtists($cancion->letra);

        return view('frontend.canciones.show', compact('cancion', 'interprete', 'interpretes', 'related', 'metaTitle', 'metaDescription', 'breadcrumbs'));
    }
}
