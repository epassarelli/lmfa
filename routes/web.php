
<?php

use Illuminate\Support\Facades\Route;

// Controladores del front
use App\Http\Controllers\Frontend\NoticiasController;
use App\Http\Controllers\Frontend\CancionesController;
use App\Http\Controllers\Frontend\DiscosController;
use App\Http\Controllers\Frontend\EntrevistasController;
use App\Http\Controllers\Frontend\FestivalesController;
use App\Http\Controllers\Frontend\InterpretesController;
use App\Http\Controllers\Frontend\MitosController;
use App\Http\Controllers\Frontend\PeniasController;
use App\Http\Controllers\Frontend\RadiosController;
use App\Http\Controllers\Frontend\RecetasController;
use App\Http\Controllers\Frontend\ShowsController;
use App\Http\Controllers\Frontend\HomeController;
use App\Http\Controllers\Frontend\ContactoController;
use App\Http\Controllers\Auth\SocialiteController;
use App\Http\Controllers\Frontend\BusquedaController;
use App\Http\Controllers\Frontend\CompartirController;

// Home
Route::get('/', [HomeController::class, 'index'])->name('home');

// Secciones generales (con slugs largos ya posicionados)
Route::get('/noticias-del-folklore-argentino', [NoticiasController::class, 'index'])->name('noticias.index');
Route::get('/noticias-del-folklore-argentino/{slug}', [NoticiasController::class, 'generalShow'])->name('noticias.show');

Route::get('/cartelera-de-eventos-folkloricos', [ShowsController::class, 'index'])->name('cartelera.index');
Route::get('/cartelera-de-eventos-folkloricos/{slug}', [ShowsController::class, 'showGeneral'])->name('cartelera.show');

Route::get('/biografias-de-artistas-folkloricos', [InterpretesController::class, 'index'])->name('interpretes.index');
Route::get('/letras-de-canciones-folkloricas', [CancionesController::class, 'index'])->name('canciones.index');
Route::get('/discografias-del-folklore-argentino', [DiscosController::class, 'index'])->name('discografias.index');

Route::get('/festivales-y-fiestas-tradicionales', [FestivalesController::class, 'index'])->name('festivales.index');
Route::get('/festivales-y-fiestas-tradicionales/{slug}', [FestivalesController::class, 'show'])->name('festivales.show');

Route::get('/radios-de-folklore-argentino', [RadiosController::class, 'index'])->name('radios.index');
Route::get('/radios-de-folklore-argentino/{slug}', [RadiosController::class, 'show'])->name('radios.show');

Route::get('/penias-folkloricas-de-argentina', [PeniasController::class, 'index'])->name('penias.index');
Route::get('/penias-folkloricas-de-argentina/{slug}', [PeniasController::class, 'show'])->name('penias.show');

Route::get('/mitos-y-leyendas-argentinas', [MitosController::class, 'index'])->name('mitos.index');
Route::get('/mitos-y-leyendas-argentinas/{slug}', [MitosController::class, 'show'])->name('mitos.show');

Route::get('/recetas-de-comidas-tipicas-argentinas', [RecetasController::class, 'index'])->name('comidas.index');
Route::get('/recetas-de-comidas-tipicas-argentinas/{slug}', [RecetasController::class, 'show'])->name('comidas.show');

// Contacto
Route::get('/contacto', [ContactoController::class, 'index'])->name('contacto');
Route::post('/contacto', [ContactoController::class, 'store'])->name('contacto.store');

// Buscador y compartir
Route::get('/buscar', [BusquedaController::class, 'index'])->name('buscar');
Route::post('/compartir', [CompartirController::class, 'store'])->name('compartir.store');

// Miniportal del artista y secciones internas
Route::get('/{interprete:slug}', [InterpretesController::class, 'show'])->name('artista.show');

Route::prefix('{interprete:slug}')->group(function () {
  Route::get('/biografia', [InterpretesController::class, 'biografia'])->name('artista.biografia');

  Route::get('/noticias', [NoticiasController::class, 'byArtista'])->name('artista.noticias');
  Route::get('/noticias/{noticia:slug}', [NoticiasController::class, 'show'])->name('artista.noticia');

  Route::get('/letras', [CancionesController::class, 'byArtista'])->name('artista.canciones');
  Route::get('/letras/{cancion:slug}', [CancionesController::class, 'show'])->name('artista.cancion');

  Route::get('/discografia', [DiscosController::class, 'byArtista'])->name('artista.discografia');
  Route::get('/discografia/{slug}', [DiscosController::class, 'show'])->name('artista.disco');

  Route::get('/shows', [ShowsController::class, 'byArtista'])->name('artista.shows');
  Route::get('/shows/{slug}', [ShowsController::class, 'show'])->name('artista.showdetalle');

  Route::get('/entrevistas', [EntrevistasController::class, 'byArtista'])->name('artista.entrevistas');
  Route::get('/entrevistas/{slug}', [EntrevistasController::class, 'show'])->name('artista.entrevista');
});

// Social Auth
Route::get('auth/google', [SocialiteController::class, 'redirectToGoogle'])->name('auth.google');
Route::get('auth/google/callback', [SocialiteController::class, 'handleGoogleCallback']);
Route::get('auth/facebook', [SocialiteController::class, 'redirectToFacebook'])->name('auth.facebook');
Route::get('auth/facebook/callback', [SocialiteController::class, 'handleFacebookCallback']);

Auth::routes();
