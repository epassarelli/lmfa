<?php

namespace App\Http\Controllers;

use App\Models\Show;
use Illuminate\Http\Request;

class ShowsController extends Controller
{
    public function index()
    {
        // Obtener las noticias en estado = 1 y ordenadas por el campo "publicar" desc
        $shows = Show::where('estado', 1)
            ->orderBy('publicar', 'desc')
            ->paginate(12);
        return view('shows.index', compact('shows'));
    }
}
