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
        $metaTitle = "Mi Folklore Argentino";
        $metaDescription = "El portal del folklore";
        return view('shows.index', compact('shows', 'metaTitle', 'metaDescription'));
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
        $metaTitle = "Mi Folklore Argentino";
        $metaDescription = "El portal del folklore";
        return view('shows.byArtista', compact('shows', 'interprete', 'metaTitle', 'metaDescription'));
    }

    public function show($slug)
    {
        $show = Show::where('slug', $slug)->firstOrFail();
        $ultimos_shows = Show::where('estado', 1)
            ->where('id', '<>', $show->id)
            ->orderByDesc('created_at')
            ->take(10)
            ->get();
        $metaTitle = "Mi Folklore Argentino";
        $metaDescription = "El portal del folklore";
        return view('shows.show', compact('show', 'ultimos_shows', 'metaTitle', 'metaDescription'));
    }
}
