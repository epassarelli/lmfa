<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\Interprete;
use App\Models\Noticia;
use App\Models\Show;
use App\Models\Disco;
use App\Models\Cancion;
use App\Models\Categoria;
use App\Models\Foto;
use App\Models\Video;
use Illuminate\Support\Facades\Session;

class NoticiasController extends Controller
{

  public function index()
  {

    // $noticia = new Noticia();
    // Obtener los últimos 5 intérpretes
    // $ultimas = $noticia->getNLast(Noticia::class, 30);

    $ultimas = Noticia::where('estado', 1)
      ->with(['categoria']) // Carga relaciones
      ->latest()
      ->take(20)
      ->get();
    // dd($ultimas);
    // $visitadas = $noticia->getNMostVisited(Noticia::class, 12);

    // $administrados = Session::get('interpretes');

    $categorias = Categoria::get();

    $metaTitle = "Noticias de Folklore Argentino: Novedades y Eventos Recientes";
    $metaDescription = "Descubre las últimas noticias del folklore argentino. Mantente al tanto de los eventos, festivales y novedades culturales más importantes. ¡Explora nuestra cobertura completa hoy mismo!";

    // Renderizar la vista con las noticias y las últimas noticias
    return view('frontend.noticias.index', compact('ultimas', 'categorias', 'metaTitle', 'metaDescription'));
  }



  public function byArtista($slug)
  {
    $interprete = Interprete::where('slug', $slug)->first();
    $noticias = $interprete->noticias()->where('estado', 1)->get();
    // dd($noticias);
    $interpretes = Interprete::getInterpretesExcluding($interprete->id);
    $section = 'noticias';

    $metaTitle = "Noticias de " . $interprete->interprete;
    $metaDescription = "Todas las novedades y noticias de " . $interprete->interprete . ". Presentaciones, próximos lanzamientos.";
    return view('frontend.noticias.byArtista', compact('noticias', 'interprete', 'interpretes', 'section', 'metaTitle', 'metaDescription'));
  }


  public function byCategoria($slug)
  {
    // Buscar la categoría por el slug
    $categoria = Categoria::where('slug', $slug)->firstOrFail();

    // Traer las noticias de esa categoría con sus intérpretes
    $noticias = Noticia::where('categoria_id', $categoria->id)
      ->where('estado', 1)
      ->with(['interpretes'])
      ->latest()
      ->paginate(10); // Paginación para mejor rendimiento

    $ultimas = Noticia::where('estado', 1)
      ->orderByDesc('created_at')
      ->take(5)
      ->get();

    // $interpretes = Interprete::getInterpretesExcluding($interprete->id);
    $section = 'noticias';
    $categorias = Categoria::get();

    $metaTitle = "Noticias de " . $categoria->nombre . " del Folklore Argentino";
    $metaDescription = "Todas las noticias de {$categoria->nombre} del folklore argentino: presentaciones en vivo, lanzamientos recientes, artistas en agenda y hechos destacados del género.";



    return view('frontend.noticias.byCategoria', compact('categoria', 'categorias', 'noticias', 'ultimas', 'section', 'metaTitle', 'metaDescription'));
  }

  // public function show($slugIterprete, $slugNoticia)
  // {
  //   $interprete = Interprete::where('slug', $slugIterprete)->first();
  //   $noticia = Noticia::where('slug', $slugNoticia)->firstOrFail();

  //   $ultimas_noticias = Noticia::where('estado', 1)
  //     ->with('interprete')
  //     ->where('id', '<>', $noticia->id)
  //     ->orderByDesc('created_at')
  //     ->take(10)
  //     ->get();
  //   // Incrementar el contador de visitas
  //   $noticia->increment('visitas');
  //   $interpretes = Interprete::getInterpretesExcluding($interprete->id);

  //   $metaTitle = strip_tags(html_entity_decode($noticia->titulo));
  //   $metaTitle = preg_replace('/\r?\n|\r/', ' ', $metaTitle);

  //   $metaDescription = Str::limit(strip_tags(html_entity_decode($noticia->noticia)), 150);
  //   // Elimina los saltos de línea
  //   $metaDescription = preg_replace('/\r?\n|\r/', ' ', $metaDescription);

  //   return view('frontend.noticias.show', compact('noticia', 'interprete', 'interpretes', 'ultimas_noticias', 'metaTitle', 'metaDescription'));
  // }


  public function show($slugIterprete, $slugNoticia)
  {

    $noticia = Noticia::where('slug', $slugNoticia)->firstOrFail();

    $ultimas_noticias = Noticia::where('estado', 1)
      // ->with('interprete')
      ->where('id', '<>', $noticia->id)
      ->orderByDesc('created_at')
      ->take(5)
      ->get();

    // Incrementar el contador de visitas
    $noticia->increment('visitas');

    // Trae 3 ultimas noticias relacionadas x categoria 
    $relacionadas = "";
    // $interprete = Interprete::where('slug', $slugIterprete)->first();
    // $interpretes = Interprete::getInterpretesExcluding($interprete->id);

    $metaTitle = strip_tags(html_entity_decode($noticia->titulo));
    $metaTitle = preg_replace('/\r?\n|\r/', ' ', $metaTitle);

    $metaDescription = Str::limit(strip_tags(html_entity_decode($noticia->noticia)), 150);
    // Elimina los saltos de línea
    $metaDescription = preg_replace('/\r?\n|\r/', ' ', $metaDescription);

    // return view('frontend.noticias.show', compact('noticia', 'interprete', 'interpretes', 'ultimas_noticias', 'metaTitle', 'metaDescription'));
    return view('frontend.noticias.show', compact('noticia', 'ultimas_noticias', 'relacionadas', 'metaTitle', 'metaDescription'));
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
