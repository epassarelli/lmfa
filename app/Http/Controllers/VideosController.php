<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class VideosController extends Controller
{
    public function index()
    {
        // Obtener las noticias en estado = 1 y ordenadas por el campo "publicar" desc
        $videos = Video::where('estado', 1)
            ->orderBy('publicar', 'desc')
            ->paginate(12);
        return view('videos.index', compact('videos'));
    }
}
