<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Interprete;

class InterpretesController extends Controller
{
    public function index()
    {
        $interprete = new Interprete();
        // Obtener los últimos 5 intérpretes
        $ultimos = $interprete->getNLast(Interprete::class, 20);
        $visitados = $interprete->getNMostVisited(Interprete::class, 20);


        // dd($interpretes);
        $metaTitle = "Biografías de Artistas del Folklore Argentino: Historia y Trayectoria";
        $metaDescription = "Conoce la historia y trayectoria de los artistas e intérpretes del folklore argentino. Descubre sus biografías completas y su contribución a la música tradicional. ¡Explora ahora!";
        // return view('home', compact('metaTitle', 'metaDescription'));
        return view('frontend.interpretes.index', compact('ultimos', 'visitados', 'metaTitle', 'metaDescription'));
    }

    public function show($slug)
    {
        // Obtener el intérprete actual
        $interprete = Interprete::where('slug', $slug)->first();
        $biografias = Interprete::where('estado', 1)->orderBy('interprete', 'ASC')->get();

        // return view('frontend.interpretes.show', compact('interprete', 'noticiasCount', 'showsCount', 'discosCount', 'cancionesCount', 'fotosCount', 'videosCount'));
        $interpretes = Interprete::getInterpretesExcluding($interprete->id);
        $section = 'biografias';

        if ($interprete) {
            // Incrementar el contador de visitas
            $interprete->increment('visitas');

            $recursos = [
                'Noticias' => 5,
                'Shows' => 2,
                'Discos' => 6,
                'Canciones' => 78,
                'Fotos' => 0,
                'Videos' => 3,
            ];

            $metaTitle = "Biografía de " . $interprete->interprete;
            $metaDescription = "Biografía de " . $interprete->interprete . " interprete del folklore argentino";
            return view('frontend.interpretes.show', compact('interprete', 'biografias', 'interpretes', 'section', 'recursos', 'metaTitle', 'metaDescription'));
        } else {
            return back()->with('alert', 'El intérprete no existe.');
        }
    }

    public function busqueda(Request $request)
    {
        $term = $request->input('q');
        $interpretes = Interprete::where('estado', 1)
            ->where('interprete', 'LIKE', "%$term%")
            ->limit(10)
            ->get(['id', 'interprete', 'slug']);

        $results = [];

        foreach ($interpretes as $interprete) {
            $results[] = [
                'id' => $interprete->id,
                'text' => $interprete->interprete
            ];
        }

        return response()->json($results);
    }

    public function letra($letra)
    {
        $interprete = new Interprete();
        // Obtener los últimos 5 intérpretes
        $ultimos = $interprete->getNLast(Interprete::class, 20);
        $visitados = $interprete->getNMostVisited(Interprete::class, 40);


        // Lógica para obtener intérpretes cuya letra del título comience con $letra
        $interpretes = Interprete::where('interprete', 'LIKE', $letra . '%')->get();

        $metaTitle = "Biografías de Interpretes folkloricos de Argentina que comienzan con $letra";
        $metaDescription = "Biografías de Interpretes folkloricos de Argentina que comienzan con $letra";

        return view('frontend.interpretes.letra', compact('interpretes', 'ultimos', 'visitados', 'letra', 'metaTitle', 'metaDescription'));
    }

    public function buscar(Request $request)
    {
        $searchTerm = $request->input('search');
        $searchColumns = ['title', 'description']; // Define aquí los campos donde deseas buscar

        $resultados = Interprete::search(Interprete::class, $searchTerm, $searchColumns);

        return view('frontend.interpretes.resultados', compact('resultados', 'searchTerm'));
    }
}
