<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\Interprete;
use App\Models\Noticia;
use App\Models\Show;
use App\Models\Disco;
use App\Models\Cancion;
use App\Models\Foto;
use App\Models\Video;
use Illuminate\Support\Facades\Session;

class NoticiasController extends Controller
{
  public function index()
  {
    // Obtener las noticias en estado = 1 y ordenadas por el campo "publicar" desc

    $noticias = Noticia::where('estado', 1)
      ->with('interprete') // Cargar la relación interprete
      ->orderBy('publicar', 'desc')
      ->paginate(12);

    // Obtener las últimas 6 noticias en estado = 1 y ordenadas por el campo "publicar" desc
    $ultimas_noticias = Noticia::where('estado', 1)
      ->with('interprete')
      ->orderBy('publicar', 'desc')
      ->take(6)
      ->get();
    $administrados = Session::get('interpretes');

    $metaTitle = "Noticias del Folklore Argentino";
    $metaDescription = "Noticias del folklore Argentino. Lanazamientos, festivales, shows y todas las novedades.";

    // Renderizar la vista con las noticias y las últimas noticias
    return view('noticias.index', compact('noticias', 'ultimas_noticias', 'administrados', 'metaTitle', 'metaDescription'));
  }

  public function byArtista($slug)
  {
    // dd($slug);
    $interprete = Interprete::where('slug', $slug)->first();
    $noticias = $interprete->noticias()->where('estado', 1)->paginate(12);
    // $noticias = Show::where('estado', 1)
    //     ->where('estado', 1)
    //     ->orderBy('publicar', 'desc')
    //     ->paginate(12);
    $metaTitle = "Noticias de " . $interprete->interprete;
    $metaDescription = "Todas las novedades y noticias de " . $interprete->interprete . ". Presentaciones, próximos lanzamientos.";
    return view('noticias.byArtista', compact('noticias', 'interprete', 'metaTitle', 'metaDescription'));
  }

  public function show($slugIterprete, $slugNoticia)
  {
    $interprete = Interprete::where('slug', $slugIterprete)->first();
    $noticia = Noticia::where('slug', $slugNoticia)->firstOrFail();

    $ultimas_noticias = Noticia::where('estado', 1)
      ->with('interprete')
      ->where('id', '<>', $noticia->id)
      ->orderByDesc('created_at')
      ->take(10)
      ->get();

    $metaTitle = strip_tags(html_entity_decode($noticia->titulo));
    $metaTitle = preg_replace('/\r?\n|\r/', ' ', $metaTitle);

    $metaDescription = Str::limit(strip_tags(html_entity_decode($noticia->noticia)), 150);
    // Elimina los saltos de línea
    $metaDescription = preg_replace('/\r?\n|\r/', ' ', $metaDescription);

    return view('noticias.show', compact('noticia', 'ultimas_noticias', 'metaTitle', 'metaDescription'));
  }


  public function showOld($slug)
  {
    // Obtener el intérprete actual
    $noticia = Noticia::where('slug', $slug)->first();

    // Obtener la lista de intérpretes en estado 1
    $interpretes = Interprete::where('estado', 1)->orderBy('interprete', 'ASC')->get();

    $metaTitle = "Mi Folklore Argentino";
    $metaDescription = "El portal del folklore";

    return view('noticias.show', compact('noticia', 'interpretes', 'metaTitle', 'metaDescription'));
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
