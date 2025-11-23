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
      ->take(100)
      ->get();
    // dd($ultimas);
    // $visitadas = $noticia->getNMostVisited(Noticia::class, 12);

    // $administrados = Session::get('interpretes');

    // Últimas 10 noticias para el sidebar
    $ultimasSidebar = Noticia::where('estado', 1)
      ->with(['categoria', 'interprete'])
      ->latest()
      ->take(10)
      ->get();

    $categorias = Categoria::get();

    $metaTitle = "Noticias de Folklore Argentino: Novedades y Eventos Recientes";
    $metaDescription = "Descubre las últimas noticias del folklore argentino. Mantente al tanto de los eventos, festivales y novedades culturales más importantes. ¡Explora nuestra cobertura completa hoy mismo!";

    // Renderizar la vista con las noticias y las últimas noticias
    return view('frontend.noticias.index', compact('ultimas', 'categorias', 'ultimasSidebar', 'metaTitle', 'metaDescription'));
  }


  public function noticias(Interprete $interprete)
  {
    $noticias = Noticia::where('estado', 1)
      ->where(function ($query) use ($interprete) {
        $query->where('interprete_id', $interprete->id)
          ->orWhereHas('interpretes', function ($q) use ($interprete) {
            $q->where('interprete_id', $interprete->id);
          });
      })
      ->orderBy('created_at', 'desc')
      ->distinct()
      ->paginate(10);

    $interpretes = Interprete::getInterpretesExcluding($interprete->id);
    $section = 'noticias';

    $metaTitle = "Noticias de " . $interprete->interprete;
    $metaDescription = "Todas las novedades y noticias de " . $interprete->interprete . ". Presentaciones, próximos lanzamientos.";
    return view('frontend.noticias.byArtista', compact('noticias', 'interprete', 'interpretes', 'section', 'metaTitle', 'metaDescription'));

    // return view('frontend.interpretes.noticias', compact('interprete', 'noticias'));
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
    $noticia = Noticia::where('slug', $slugNoticia)
      ->with(['categoria', 'interprete', 'interpretes'])
      ->firstOrFail();

    $ultimas_noticias = Noticia::where('estado', 1)
      ->where('id', '<>', $noticia->id)
      ->orderByDesc('created_at')
      ->take(5)
      ->get();

    // Incrementar el contador de visitas
    $noticia->increment('visitas');

    // Obtener 3 noticias relacionadas
    // Prioridad: misma categoría, mismo intérprete principal, o intérpretes secundarios comunes
    $relacionadas = Noticia::where('estado', 1)
      ->where('id', '<>', $noticia->id)
      ->where(function ($query) use ($noticia) {
        // Por categoría
        if ($noticia->categoria_id) {
          $query->where('categoria_id', $noticia->categoria_id);
        }
        // Por intérprete principal
        if ($noticia->interprete_id) {
          $query->orWhere('interprete_id', $noticia->interprete_id);
        }
        // Por intérpretes secundarios (si existen)
        $interpreteIds = $noticia->interpretes->pluck('id')->toArray();
        if (!empty($interpreteIds)) {
          $query->orWhereHas('interpretes', function ($q) use ($interpreteIds) {
            $q->whereIn('interprete_id', $interpreteIds);
          });
        }
      })
      ->with(['categoria', 'interprete'])
      ->orderByDesc('created_at')
      ->distinct()
      ->take(3)
      ->get();

    $metaTitle = strip_tags(html_entity_decode($noticia->titulo));
    $metaTitle = preg_replace('/\r?\n|\r/', ' ', $metaTitle);

    $metaDescription = Str::limit(strip_tags(html_entity_decode($noticia->noticia)), 150);
    // Elimina los saltos de línea
    $metaDescription = preg_replace('/\r?\n|\r/', ' ', $metaDescription);

    // Últimas 10 noticias para el sidebar (excluyendo la noticia actual)
    $ultimasSidebar = Noticia::where('estado', 1)
      ->where('id', '<>', $noticia->id)
      ->with(['categoria', 'interprete'])
      ->latest()
      ->take(10)
      ->get();

    return view('frontend.noticias.show', compact('noticia', 'ultimas_noticias', 'relacionadas', 'ultimasSidebar', 'metaTitle', 'metaDescription'));
  }


  public function generalShow($slug)
  {
    $noticia = Noticia::where('slug', $slug)->with('interprete', 'categoria')->firstOrFail();

    // Incrementar el contador de visitas
    $noticia->increment('visitas');

    $canonical = $noticia->interprete
      ? route('artista.noticia', [$noticia->interprete->slug, $noticia->slug])
      : route('noticias.show', $noticia->slug);

    $metaTitle = strip_tags(html_entity_decode($noticia->titulo));
    $metaTitle = preg_replace('/\r?\n|\r/', ' ', $metaTitle);

    $metaDescription = Str::limit(strip_tags(html_entity_decode($noticia->noticia)), 150);
    // Elimina los saltos de línea
    $metaDescription = preg_replace('/\r?\n|\r/', ' ', $metaDescription);

    return view('frontend.noticias.show', compact('noticia', 'canonical', 'metaTitle', 'metaDescription'));
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
