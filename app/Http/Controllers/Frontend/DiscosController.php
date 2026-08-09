<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Album;
use App\Models\Interprete;
use App\Support\SeoMetadata;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class DiscosController extends Controller
{
    public function index()
    {
        $discos = Album::query()
            ->where('estado', 1)
            ->with([
                'interprete:id,interprete,slug',
                'images',
            ])
            ->orderByDesc('created_at')
            ->simplePaginate(24);

        $metaTitle = 'Discografias de Folklore Argentino: Albumes y Obras Destacadas';
        $metaDescription = 'Explora las discografias completas del folklore argentino. Encuentra albumes y canciones clasicas de artistas destacados.';

        $breadcrumbs = [
            ['label' => 'Discos', 'url' => route('discografias.index')],
        ];

        return view('frontend.discos.index', compact('discos', 'metaTitle', 'metaDescription', 'breadcrumbs'));
    }

    public function byArtista($slug)
    {
        $interprete = Interprete::where('slug', $slug)->first();
        $discos = $interprete->discos()->where('estado', 1)->with('images')->orderby('anio', 'desc')->get();
        $interpretes = Interprete::getInterpretesExcluding($interprete->id);
        $section = 'discografias';

        $metaTitle = 'Discografia de '.$interprete->interprete;
        $metaDescription = "Discografia completa de {$interprete->interprete}, figura destacada del folklore argentino. Conoce sus albumes, canciones y trayectoria musical.";

        $breadcrumbs = [
            ['label' => 'Artistas', 'url' => route('interpretes.index')],
            ['label' => $interprete->interprete, 'url' => route('artista.show', $interprete->slug)],
            ['label' => 'Discos'],
        ];

        return view('frontend.discos.byArtista', compact('discos', 'interprete', 'interpretes', 'section', 'metaTitle', 'metaDescription', 'breadcrumbs'));
    }

    public function show($slugInterprete, $slugDisco)
    {
        $interprete = Interprete::where('slug', $slugInterprete)->first();
        $disco = Album::where('slug', $slugDisco)->with('images')->firstOrFail();

        $disco->increment('visitas');

        $interpretes = Interprete::getInterpretesExcluding($interprete->id);
        $related = $interprete->getRelatedContent($interprete, 'discos', $disco, 'anio', 'desc');

        $seo = SeoMetadata::album($disco, $interprete);
        $metaTitle = $seo['title'];
        $metaDescription = $seo['description'];
        $h1 = $seo['h1'];

        $breadcrumbs = [
            ['label' => 'Artistas', 'url' => route('interpretes.index')],
            ['label' => $interprete->interprete, 'url' => route('artista.show', $interprete->slug)],
            ['label' => 'Discos', 'url' => route('artista.discografia', $interprete->slug)],
            ['label' => $disco->album],
        ];

        return view('frontend.discos.show', compact('disco', 'interprete', 'interpretes', 'related', 'metaTitle', 'metaDescription', 'h1', 'breadcrumbs'));
    }
}
