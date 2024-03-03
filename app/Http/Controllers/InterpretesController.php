<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Interprete;
// use App\Models\Noticia;
// use App\Models\Show;
// use App\Models\Disco;
// use App\Models\Cancion;
// use App\Models\Foto;
// use App\Models\Video;


class InterpretesController extends Controller
{
    public function index()
    {
        $interpretes = Interprete::where('estado', 1)
            ->orderBy('visitas', 'desc')
            ->paginate(12);

        // dd($interpretes);
        $metaTitle = "Mi Folklore Argentino";
        $metaDescription = "El portal del folklore";
        // return view('home', compact('metaTitle', 'metaDescription'));
        return view('interpretes.index', compact('interpretes', 'metaTitle', 'metaDescription'));
    }

    public function show($slug)
    {
        // Obtener el intérprete actual
        $interprete = Interprete::where('slug', $slug)->first();
        // $interprete = Interprete::where('slug', $slug)->firstOrFail();
        //dd($interprete);
        // Obtener la lista de intérpretes en estado 1
        $interpretes = Interprete::where('estado', 1)->orderBy('interprete', 'ASC')->get();

        // return view('interpretes.show', compact('interprete', 'noticiasCount', 'showsCount', 'discosCount', 'cancionesCount', 'fotosCount', 'videosCount'));


        if ($interprete) {

            // $noticias_count = Noticia::where('interprete_id', $interprete->id)->count();
            // $noticiasCount = Noticia::whereHas('interpretes', function ($query) use ($interprete) {
            //     $query->where('interprete_id', $interprete->id);
            // })->count();

            // $shows_count = Show::where('interprete_id', $interprete->id)->count();
            // $showsCount = Show::whereHas('interpretes', function ($query) use ($interprete) {
            //     $query->where('interprete_id', $interprete->id);
            // })->count();

            // $discos_count = Disco::where('interprete_id', $interprete->id)->count();
            // $discosCount = Disco::whereHas('interpretes', function ($query) use ($interprete) {
            //     $query->where('interprete_id', $interprete->id);
            // })->count();

            // $canciones_count = Cancion::where('interprete_id', $interprete->id)->count();
            // $cancionesCount = Cancion::whereHas('interpretes', function ($query) use ($interprete) {
            //     $query->where('interprete_id', $interprete->id);
            // })->count();

            // $fotos_count = Foto::where('interprete_id', $interprete->id)->count();
            // $fotosCount = Foto::whereHas('interpretes', function ($query) use ($interprete) {
            //     $query->where('interprete_id', $interprete->id);
            // })->count();

            // $videos_count = Video::where('interprete_id', $interprete->id)->count();
            // $videosCount = Video::whereHas('interpretes', function ($query) use ($interprete) {
            //     $query->where('interprete_id', $interprete->id);
            // })->count();

            // $recursos = [
            //     'Noticias' => $noticias_count,
            //     'Shows' => $shows_count,
            //     'Discos' => $discos_count,
            //     'Canciones' => $canciones_count,
            //     'Fotos' => $fotos_count,
            //     'Videos' => $videos_count,
            // ];

            $recursos = [
                'Noticias' => 5,
                'Shows' => 2,
                'Discos' => 6,
                'Canciones' => 78,
                'Fotos' => 0,
                'Videos' => 3,
            ];

            // return view('interpretes.show', compact('interprete', 'noticiasCount', 'showsCount', 'discosCount', 'cancionesCount', 'fotosCount', 'videosCount'));
            // dd($interprete);
            $metaTitle = "Mi Folklore Argentino";
            $metaDescription = "El portal del folklore";
            return view('interpretes.show', compact('interprete', 'interpretes', 'recursos', 'metaTitle', 'metaDescription'));
            // return view('interpretes.show', compact('interprete', 'interpretes'));
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
}
