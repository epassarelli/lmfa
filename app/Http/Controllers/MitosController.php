<?php

namespace App\Http\Controllers;

use App\Models\Mito;
use Illuminate\Http\Request;

class MitosController extends Controller
{
    public function index()
    {
        // Obtener las noticias en estado = 1 y ordenadas por el campo "publicar" desc
        $mitos = Mito::where('estado', 1)
            ->orderBy('publicar', 'desc')
            ->paginate(12);
        $metaTitle = "Mi Folklore Argentino";
        $metaDescription = "El portal del folklore";
        return view('mitos.index', compact('mitos', 'metaTitle', 'metaDescription'));
    }

    public function show($slug)
    {
        $mito = Mito::where('slug', $slug)->firstOrFail();
        $ultimos_mitos = Mito::where('estado', 1)
            ->where('id', '<>', $mito->id)
            ->orderByDesc('created_at')
            ->take(10)
            ->get();
        $metaTitle = "Mi Folklore Argentino";
        $metaDescription = "El portal del folklore";
        return view('mitos.show', compact('mito', 'ultimos_mitos', 'metaTitle', 'metaDescription'));
    }
}
