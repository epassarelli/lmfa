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



// Rutas para el controlador de biografia
Route::get('biografias/letra/{letra}', [InterpretesController::class, 'letra'])->name('interprete.letra');
Route::get('biografias/{interprete:slug}', [InterpretesController::class, 'show'])->name('interprete.show');
Route::get('biografias', [InterpretesController::class, 'index'])->name('interpretes.index');

// Rutas para el controlador de Noticias:
Route::get('noticias/{interprete:slug}/{noticia:slug}', [NoticiasController::class, 'show'])->name('interprete.noticia.show');
Route::get('noticias/{interprete:slug}', [NoticiasController::class, 'byArtista'])->name('interprete.noticias');
Route::get('noticias', [NoticiasController::class, 'index'])->name('noticias.index');

// Rutas para el controlador de Letras de canciones:
Route::get('letras-de-canciones/{interprete:slug}/{cancion:slug}', [CancionesController::class, 'show'])->name('canciones.show');
Route::get('letras-de-canciones/{interprete:slug}', [CancionesController::class, 'byArtista'])->name('interprete.canciones');
Route::get('letras-de-canciones', [CancionesController::class, 'index'])->name('canciones.index');

// Rutas para el controlador de discografías:
Route::get('discografias/{interprete:slug}/{id}', [DiscosController::class, 'show'])->name('interprete.album.show');
Route::get('discografias/{interprete:slug}', [DiscosController::class, 'byArtista'])->name('interprete.discografia');
Route::get('discografias', [DiscosController::class, 'index'])->name('discos.index');

// Rutas para el controlador de Cartelera:
Route::get('cartelera/{interprete:slug}/{id}',  [ShowsController::class, 'show'])->name('interprete.show.show');
Route::get('cartelera/{interprete:slug}', [ShowsController::class, 'byArtista'])->name('interprete.shows');
Route::get('cartelera', [ShowsController::class, 'index'])->name('shows.index');


// Route::get('thumb/{carpeta}/{ancho}/{alto}/{calidad}', [ImagenController::class, 'generarMiniaturas']);

// Rutas para el controlador de Festivales:
Route::get('fiestas-tradicionales-argentina/{id}', [FestivalesController::class, 'show'])->name('festivales.show');
Route::get('fiestas-tradicionales-argentina', [FestivalesController::class, 'index'])->name('festivales.index');

// Rutas para el controlador de Radios:
Route::get('radios/{id}', [RadiosController::class, 'show'])->name('radios.show');
Route::get('radios', [RadiosController::class, 'index'])->name('radios.index');

// Rutas para el controlador de Peñas:
Route::get('penias/{id}', [PeniasController::class, 'show'])->name('penas.show');
Route::get('penias', [PeniasController::class, 'index'])->name('penas.index');

// Rutas para el controlador de Comidas:
Route::get('comidas/letra/{letra}', [RecetasController::class, 'letra'])->name('comidas.letra');
Route::get('comidas/{id}', [RecetasController::class, 'show'])->name('comidas.show');
Route::get('comidas', [RecetasController::class, 'index'])->name('comidas.index');

// Rutas para el controlador de Mitos:
Route::get('mitos/letra/{letra}', [MitosController::class, 'letra'])->name('mitos.letra');
Route::get('mitos/{id}', [MitosController::class, 'show'])->name('mitos.show');
Route::get('mitos', [MitosController::class, 'index'])->name('mitos.index');


// Rutas para el controlador de entrevistas
Route::get('{interprete:slug}/entrevistas', [EntrevistasController::class, 'byArtista'])->name('interprete.entrevistas');
Route::get('{interprete:slug}/entrevistas/{id}', [EntrevistasController::class, 'show'])->name('interprete.entrevista.show');

Route::get('/', [HomeController::class, 'index'])->name('home');


// Auth::routes();
Auth::routes(['register' => false, 'reset' => false]);
