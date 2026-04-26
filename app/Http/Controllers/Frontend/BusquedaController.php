<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Models\News;
use App\Models\Interprete;
use App\Models\Album;
use App\Models\Cancion;
use App\Models\Festival;
use App\Models\Event;
use App\Models\Comida;
use App\Models\Mito;

class BusquedaController extends Controller
{
    public function index(Request $request)
    {
        $query = $request->input('q');

        $resultados = [
            'noticias' => News::where('title', 'like', "%$query%")
                ->orWhere('body', 'like', "%$query%")
                ->orderBy('id', 'desc')->take(5)->get(),

            'biografias' => Interprete::where('interprete', 'like', "%$query%")
                ->orderBy('id', 'desc')->take(5)->get(),

            'discos' => Album::where('album', 'like', "%$query%")
                ->orderBy('id', 'desc')->take(5)->get(),

            'canciones' => Cancion::where('cancion', 'like', "%$query%")
                ->orWhere('letra', 'like', "%$query%")
                ->orderBy('id', 'desc')->take(5)->get(),

            'festivales' => Festival::where('titulo', 'like', "%$query%")
                ->orderBy('id', 'desc')->take(5)->get(),

            'shows' => Event::where('title', 'like', "%$query%")
                ->orWhere('body', 'like', "%$query%")
                ->orderBy('id', 'desc')->take(5)->get(),

            'recetas' => Comida::where('titulo', 'like', "%$query%")
                ->orderBy('id', 'desc')->take(5)->get(),

            'mitos' => Mito::where('titulo', 'like', "%$query%")
                ->orderBy('id', 'desc')->take(5)->get(),
        ];


        return view('frontend.busqueda.resultados', compact('query', 'resultados'));
    }
}
