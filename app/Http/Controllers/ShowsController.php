<?php

namespace App\Http\Controllers;

use App\Models\Interprete;
use App\Models\Show;
use Illuminate\Http\Request;

class ShowsController extends Controller
{
    public function index()
    {
        $shows = Show::where('estado', 1)
            ->orderBy('publicar', 'desc')
            ->paginate(12);
        return view('shows.index', compact('shows'));
    }

    public function byArtista($slug)
    {
        // dd($slug);
        $interprete = Interprete::where('slug', $slug)->first();
        $shows = $interprete->shows()->paginate(12);
        // $shows = Show::where('estado', 1)
        //     ->where('estado', 1)
        //     ->orderBy('publicar', 'desc')
        //     ->paginate(12);
        return view('shows.byArtista', compact('shows', 'interprete'));
    }

    public function show($slug)
    {
        $show = Show::where('slug', $slug)->firstOrFail();
        $ultimos_shows = Show::where('estado', 1)
            ->where('id', '<>', $show->id)
            ->orderByDesc('created_at')
            ->take(10)
            ->get();

        return view('shows.show', compact('show', 'ultimos_shows'));
    }
}
