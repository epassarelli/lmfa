<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Support\CanonicalUrl;

/**
 * Página pública "Cómo publicar". Sin datos por usuario: la cachea la caché de páginas públicas.
 */
class PublishGuideController extends Controller
{
    public function show()
    {
        return view('frontend.guia.como-publicar', [
            'metaTitle' => 'Cómo publicar en Mi Folklore Argentino | Guía para artistas y organizadores',
            'metaDescription' => 'Paso a paso para cargar tu artista, festival, disco, canción, noticia o evento en Mi Folklore Argentino: creá tu cuenta, vinculá tu ficha y publicá.',
            'canonical' => CanonicalUrl::normalize(route('publish.guide', [], false), false),
        ]);
    }
}
