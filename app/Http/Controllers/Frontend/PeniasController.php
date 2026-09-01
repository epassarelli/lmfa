<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Penia;
use Illuminate\Support\Str;

class PeniasController extends Controller
{
    public function index()
    {
        // Obtener las noticias en estado = 1 y ordenadas por el campo "publicar" desc
        $penias = Penia::where('estado', 1)
            ->with('images')
            ->orderBy('publicar', 'desc')
            ->paginate(12);
        
        $metaTitle = "Peñas Folkloricas de Argentina: Espacios de Encuentro y Tradición";
        $metaDescription = "Descubrí las peñas y centros culturales donde se vive el folklore en Argentina. Lugares para cantar, bailar y disfrutar de nuestra cultura.";

        $breadcrumbs = [
            ['label' => 'Peñas', 'url' => route('penias.index')],
        ];

        return view('frontend.penias.index', compact('penias', 'metaTitle', 'metaDescription', 'breadcrumbs'));
    }

    public function show(string $slug)
    {
        $penia = Penia::query()
            ->where('slug', $slug)
            ->where('estado', 1)
            ->with('images')
            ->firstOrFail();

        $penia->increment('visitas');

        $metaTitle = $penia->titulo.' | Peña Folklórica Argentina';
        $metaDescription = Str::limit(
            trim(preg_replace('/\s+/', ' ', strip_tags($penia->detalle))),
            160,
            ''
        );
        $breadcrumbs = [
            ['label' => 'Peñas', 'url' => route('penias.index')],
            ['label' => $penia->titulo],
        ];

        return view('frontend.penias.show', compact('penia', 'metaTitle', 'metaDescription', 'breadcrumbs'));
    }
}
