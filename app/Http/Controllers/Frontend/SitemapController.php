<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use App\Models\Noticia;
use App\Models\Interprete;
use App\Models\Show;
use App\Models\Album;
use App\Models\Cancion;
use App\Models\Festival;
use App\Models\Radio;
use App\Models\Penia;
use App\Models\Mito;
use App\Models\Comida;

class SitemapController extends Controller
{
  public function index()
  {
    $urls = [];

    // Static Pages
    $urls[] = ['url' => route('home'), 'priority' => '1.0', 'changefreq' => 'daily'];
    $urls[] = ['url' => route('contacto'), 'priority' => '0.5', 'changefreq' => 'monthly'];
    $urls[] = ['url' => route('noticias.index'), 'priority' => '0.8', 'changefreq' => 'daily'];
    $urls[] = ['url' => route('cartelera.index'), 'priority' => '0.8', 'changefreq' => 'daily'];
    $urls[] = ['url' => route('interpretes.index'), 'priority' => '0.8', 'changefreq' => 'weekly'];
    $urls[] = ['url' => route('canciones.index'), 'priority' => '0.8', 'changefreq' => 'weekly'];
    $urls[] = ['url' => route('discografias.index'), 'priority' => '0.8', 'changefreq' => 'weekly'];
    $urls[] = ['url' => route('festivales.index'), 'priority' => '0.8', 'changefreq' => 'weekly'];
    $urls[] = ['url' => route('radios.index'), 'priority' => '0.8', 'changefreq' => 'monthly'];
    $urls[] = ['url' => route('penias.index'), 'priority' => '0.8', 'changefreq' => 'monthly'];
    $urls[] = ['url' => route('mitos.index'), 'priority' => '0.8', 'changefreq' => 'monthly'];
    $urls[] = ['url' => route('comidas.index'), 'priority' => '0.8', 'changefreq' => 'monthly'];

    // Dynamic Content
    // Noticias
    $noticias = Noticia::where('estado', 1)->latest()->get();
    foreach ($noticias as $noticia) {
      $urls[] = [
        'url' => route('noticias.show', $noticia->slug),
        'priority' => '0.9',
        'changefreq' => 'weekly',
        'lastmod' => $noticia->updated_at->toAtomString(),
      ];
    }

    // Interpretes
    $interpretes = Interprete::where('estado', 1)->get();
    foreach ($interpretes as $interprete) {
      $urls[] = [
        'url' => route('artista.show', $interprete->slug),
        'priority' => '0.8',
        'changefreq' => 'monthly',
        'lastmod' => $interprete->updated_at->toAtomString(),
      ];
    }

    // Shows
    $shows = Show::where('estado', 1)->where('fecha', '>=', now())->get();
    foreach ($shows as $show) {
      $urls[] = [
        'url' => route('cartelera.show', $show->slug),
        'priority' => '0.8',
        'changefreq' => 'weekly',
        'lastmod' => $show->updated_at->toAtomString(),
      ];
    }

    // Discos
    $discos = Album::where('estado', 1)->get();
    foreach ($discos as $disco) {
      $urls[] = [
        'url' => route('artista.disco', ['interprete' => $disco->interprete->slug ?? 'varios', 'slug' => $disco->slug]),
        'priority' => '0.7',
        'changefreq' => 'monthly',
        'lastmod' => $disco->updated_at->toAtomString(),
      ];
    }

    // Canciones
    $canciones = Cancion::where('estado', 1)->get();
    foreach ($canciones as $cancion) {
      // Assuming route is artista.cancion which needs interprete slug
      $interpreteSlug = $cancion->interprete->slug ?? 'varios';
      $urls[] = [
        'url' => route('artista.cancion', ['interprete' => $interpreteSlug, 'cancion' => $cancion->slug]),
        'priority' => '0.7',
        'changefreq' => 'monthly',
        'lastmod' => $cancion->updated_at->toAtomString(),
      ];
    }

    // Festivales
    $festivales = Festival::where('estado', 1)->get();
    foreach ($festivales as $festival) {
      $urls[] = [
        'url' => route('festivales.show', $festival->slug),
        'priority' => '0.8',
        'changefreq' => 'monthly',
        'lastmod' => $festival->updated_at->toAtomString(),
      ];
    }

    // Radios
    $radios = Radio::where('estado', 1)->get();
    foreach ($radios as $radio) {
      $urls[] = [
        'url' => route('radios.show', $radio->slug),
        'priority' => '0.6',
        'changefreq' => 'monthly',
        'lastmod' => $radio->updated_at->toAtomString(),
      ];
    }

    // Penias
    $penias = Penia::where('estado', 1)->get();
    foreach ($penias as $penia) {
      $urls[] = [
        'url' => route('penias.show', $penia->slug),
        'priority' => '0.6',
        'changefreq' => 'monthly',
        'lastmod' => $penia->updated_at->toAtomString(),
      ];
    }

    // Mitos
    $mitos = Mito::where('estado', 1)->get();
    foreach ($mitos as $mito) {
      $urls[] = [
        'url' => route('mitos.show', $mito->slug),
        'priority' => '0.7',
        'changefreq' => 'monthly',
        'lastmod' => $mito->updated_at->toAtomString(),
      ];
    }

    // Recetas
    $recetas = Comida::where('estado', 1)->get();
    foreach ($recetas as $receta) {
      $urls[] = [
        'url' => route('comidas.show', $receta->slug),
        'priority' => '0.7',
        'changefreq' => 'monthly',
        'lastmod' => $receta->updated_at->toAtomString(),
      ];
    }

    $content = view('sitemap', compact('urls'))->render();

    return Response::make($content, 200, ['Content-Type' => 'text/xml']);
  }
}
