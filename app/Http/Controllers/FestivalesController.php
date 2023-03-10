<?php

namespace App\Http\Controllers;

use App\Models\Festival;
use Illuminate\Http\Request;

class FestivalesController extends Controller
{
    public function index()
    {
        // Obtener las noticias en estado = 1 y ordenadas por el campo "publicar" desc
        $festivales = Festival::where('estado', 1)
            ->orderBy('publicar', 'desc')
            ->paginate(12);
        return view('festivales.index', compact('festivales'));
    }

    public function show($slug)
    {
        $festival = Festival::where('slug', $slug)->firstOrFail();
        $ultimos_festivales = Festival::where('estado', 1)
            ->where('id', '<>', $festival->id)
            ->orderByDesc('created_at')
            ->take(10)
            ->get();

        return view('festivales.show', compact('festival', 'ultimos_festivales'));
    }
}
