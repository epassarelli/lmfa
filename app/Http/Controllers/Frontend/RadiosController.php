<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Radio;
use Illuminate\Support\Str;

class RadiosController extends Controller
{
    public function index()
    {
        // Obtener las noticias en estado = 1 y ordenadas por el campo "publicar" desc
        $radios = Radio::where('estado', 1)
            ->with('images')
            ->orderBy('publicar', 'desc')
            ->paginate(12);
        
        $metaTitle = "Radios de Folklore Argentino: Escuchá Nuestra Música en Vivo";
        $metaDescription = "Directorio de radios que transmiten folklore argentino. Sintonizá la mejor música tradicional y programas culturales de todo el país.";

        $breadcrumbs = [
            ['label' => 'Radios', 'url' => route('radios.index')]
        ];

        return view('frontend.radios.index', compact('radios', 'metaTitle', 'metaDescription', 'breadcrumbs'));
    }

    public function show(string $slug)
    {
        $radio = Radio::query()
            ->where('slug', $slug)
            ->where('estado', 1)
            ->with('images')
            ->firstOrFail();

        $radio->increment('visitas');

        $metaTitle = $radio->titulo.' | Radio de Folklore Argentino';
        $metaDescription = Str::limit(
            trim(preg_replace('/\s+/', ' ', strip_tags($radio->detalle))),
            160,
            ''
        );
        $breadcrumbs = [
            ['label' => 'Radios', 'url' => route('radios.index')],
            ['label' => $radio->titulo],
        ];

        return view('frontend.radios.show', compact('radio', 'metaTitle', 'metaDescription', 'breadcrumbs'));
    }
}
