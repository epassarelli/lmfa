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
// use App\Http\Controllers\Frontend\ImagenController;

use App\Http\Controllers\Auth\SocialiteController;


// Folklore Argentino
// Letras de canciones folklóricas
// Noticias de folklore argentino
// Discografías del folklore argentino
// Festivales y fiestas tradicionales
// Biografías de artistas folklóricos
// Mitos y leyendas argentinas
// Recetas de comidas típicas argentinas
// Cartelera de eventos folklóricos

// Rutas para el controlador de biografia
Route::get('biografias-de-artistas-folkloricos/letra/{letra}', [InterpretesController::class, 'letra'])->name('interprete.letra');
Route::get('biografias-de-artistas-folkloricos/{interprete:slug}', [InterpretesController::class, 'show'])->name('interprete.show');
Route::get('biografias-de-artistas-folkloricos', [InterpretesController::class, 'index'])->name('interpretes.index');

// Rutas para el controlador de Noticias:
Route::get('noticias-del-folklore-argentino/{categoria:slug}/{noticia:slug}', [NoticiasController::class, 'show'])->name('noticia.show');
// Route::get('noticias-del-folklore-argentino/{interprete:slug}/{noticia:slug}', [NoticiasController::class, 'show'])->name('interprete.noticia.show');
Route::get('noticias-del-folklore-argentino/{categoria:slug}', [NoticiasController::class, 'byCategoria'])->name('noticias.byCategoria');
Route::get('noticias-del-folklore-argentino/{interprete:slug}', [NoticiasController::class, 'byArtista'])->name('interprete.noticias');
Route::get('noticias-del-folklore-argentino', [NoticiasController::class, 'index'])->name('noticias.index');

// Rutas para el controlador de Letras de canciones:
Route::get('letras-de-canciones-folkloricas/{interprete:slug}/{cancion:slug}', [CancionesController::class, 'show'])->name('canciones.show');
Route::get('letras-de-canciones-folkloricas/{interprete:slug}', [CancionesController::class, 'byArtista'])->name('interprete.canciones');
Route::get('letras-de-canciones-folkloricas', [CancionesController::class, 'index'])->name('canciones.index');

// Rutas para el controlador de discografías:
Route::get('discografias-del-folklore-argentino/{interprete:slug}/{id}', [DiscosController::class, 'show'])->name('interprete.album.show');
Route::get('discografias-del-folklore-argentino/{interprete:slug}', [DiscosController::class, 'byArtista'])->name('interprete.discografia');
Route::get('discografias-del-folklore-argentino', [DiscosController::class, 'index'])->name('discos.index');

// Rutas para el controlador de Cartelera:
Route::get('cartelera-de-eventos-folkloricos/{interprete:slug}/{id}',  [ShowsController::class, 'show'])->name('interprete.show.show');
Route::get('cartelera-de-eventos-folkloricos/{interprete:slug}', [ShowsController::class, 'byArtista'])->name('interprete.shows');
Route::get('cartelera-de-eventos-folkloricos', [ShowsController::class, 'index'])->name('shows.index');


// Route::get('thumb/{carpeta}/{ancho}/{alto}/{calidad}', [ImagenController::class, 'generarMiniaturas']);

// Rutas para el controlador de Festivales:
Route::get('festivales-y-fiestas-tradicionales/{id}', [FestivalesController::class, 'show'])->name('festivales.show');
Route::get('festivales-y-fiestas-tradicionales', [FestivalesController::class, 'index'])->name('festivales.index');

// Rutas para el controlador de Radios:
Route::get('radios-de-folklore-argentino/{id}', [RadiosController::class, 'show'])->name('radios.show');
Route::get('radios-de-folklore-argentino', [RadiosController::class, 'index'])->name('radios.index');

// Rutas para el controlador de Peñas:
Route::get('penias-folkloricas-de-argentina/{id}', [PeniasController::class, 'show'])->name('penas.show');
Route::get('penias-folkloricas-de-argentina', [PeniasController::class, 'index'])->name('penas.index');

// Rutas para el controlador de Comidas:
Route::get('recetas-de-comidas-tipicas-argentinas/letra/{letra}', [RecetasController::class, 'letra'])->name('comidas.letra');
Route::get('recetas-de-comidas-tipicas-argentinas/{id}', [RecetasController::class, 'show'])->name('comidas.show');
Route::get('recetas-de-comidas-tipicas-argentinas', [RecetasController::class, 'index'])->name('comidas.index');

// Rutas para el controlador de Mitos:
Route::get('mitos-y-leyendas-argentinas/letra/{letra}', [MitosController::class, 'letra'])->name('mitos.letra');
Route::get('mitos-y-leyendas-argentinas/{id}', [MitosController::class, 'show'])->name('mitos.show');
Route::get('mitos-y-leyendas-argentinas', [MitosController::class, 'index'])->name('mitos.index');


// Rutas para el controlador de entrevistas
// Route::get('{interprete:slug}/entrevistas', [EntrevistasController::class, 'byArtista'])->name('interprete.entrevistas');
// Route::get('{interprete:slug}/entrevistas/{id}', [EntrevistasController::class, 'show'])->name('interprete.entrevista.show');

Route::get('/', [HomeController::class, 'index'])->name('home');

// Rutas para la Autenticacion con redes sociales

Route::get('auth/google', [SocialiteController::class, 'redirectToGoogle'])->name('auth.google');
Route::get('auth/google/callback', [SocialiteController::class, 'handleGoogleCallback']);

Route::get('auth/facebook', [SocialiteController::class, 'redirectToFacebook'])->name('auth.facebook');
Route::get('auth/facebook/callback', [SocialiteController::class, 'handleFacebookCallback']);

Auth::routes();
//Auth::routes(['register' => false, 'reset' => false]);
