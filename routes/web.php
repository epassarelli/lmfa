<?php

use Illuminate\Support\Facades\Route;

// Controladores del front
use App\Http\Controllers\NoticiasController;
use App\Http\Controllers\CancionesController;
use App\Http\Controllers\DiscosController;
use App\Http\Controllers\EntrevistasController;
use App\Http\Controllers\FestivalesController;
use App\Http\Controllers\InterpretesController;
use App\Http\Controllers\MitosController;
use App\Http\Controllers\PeniasController;
use App\Http\Controllers\RadiosController;
use App\Http\Controllers\RecetasController;
use App\Http\Controllers\ShowsController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ImagenController;

// use App\Http\Controllers\Dashboard\InterpretesCRUD;

// use App\Http\Controllers\VideosController;
use App\Http\Livewire\Backend\Dashboard;
use App\Http\Livewire\Backend\Noticias;
use App\Http\Livewire\Backend\Interpretes;
use App\Http\Livewire\Backend\Users;

// Route::resource('dashboard/interpretes', InterpretesCRUD::class);


// Rutas para el controlador de biografia
Route::get('biografias/letra/{letra}', [InterpretesController::class, 'letra'])->name('interprete.letra');
Route::get('biografias/{interprete:slug}', [InterpretesController::class, 'show'])->name('interprete.show');
Route::get('biografias', [InterpretesController::class, 'index'])->name('interpretes.index');

// Rutas para el controlador de Noticias:
Route::get('noticias/{interprete:slug}/{noticia:slug}', [NoticiasController::class, 'show'])->name('interprete.noticia.show');
Route::get('noticias/{interprete:slug}', [NoticiasController::class, 'byArtista'])->name('interprete.noticias');
// Route::get('noticias/{id}', [NoticiasController::class, 'show'])->name('noticias.show');
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


Route::get('thumb/{carpeta}/{ancho}/{alto}/{calidad}', [ImagenController::class, 'generarMiniaturas']);

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





// Rutas para el controlador de noticias:
// Route::prefix('noticias')->group(function () {
//     Route::get('/noticias-de-{slug}', [NoticiasController::class, 'byInterprete'])->name('noticias.byInterprete');
//     Route::get('/{slug}', [NoticiasController::class, 'show'])->name('noticias.show');
//     Route::get('/busqueda', [NoticiasController::class, 'busqueda'])->name('noticias.busqueda');
//     //Route::get('/', [NoticiasController::class, 'index'])->name('noticias.index');
// });


Route::middleware([
  'auth:sanctum',
  config('jetstream.auth_session'),
  'verified'
])->group(function () {

  Route::prefix('admin')->group(function () {
    Route::get('/dashboard', Dashboard::class)->name('admin.dashboard');
    Route::get('/usuarios', Users::class)->name('admin.usuarios');
    Route::get('/interpretes', Interpretes::class)->name('admin.interpretes');
    //         Route::get('/noticias', Noticias::class)->name('admin.noticias');
    // Route::get('/noticias', Noticias::class)->name('admin.shows');
    // Route::get('/noticias', Noticias::class)->name('admin.discos');
    // Route::get('/noticias', Noticias::class)->name('admin.canciones');
    // Route::get('/noticias', Noticias::class)->name('admin.entrevistas');
    // Route::get('/noticias', Noticias::class)->name('admin.videos');

    // Route::get('/noticias', Noticias::class)->name('admin.festivales');
    // Route::get('/noticias', Noticias::class)->name('admin.radios');
    // Route::get('/noticias', Noticias::class)->name('admin.penias');
    // Route::get('/noticias', Noticias::class)->name('admin.comidas');
    // Route::get('/noticias', Noticias::class)->name('admin.mitos');
    // Route::get('/articulos', Noticias::class)->name('admin.articulos');
  });
});





// Rutas para el controlador de Cartelera:
// Route::get('videos', [VideosController::class, 'index'])->name('videos.index');


#############################################################################
##  Rutas internas al SILO Interpretes 
#############################################################################
// Rutas para el controlador de interpretes:
// Route::prefix('interpretes')->group(function () {
//     // Route::get('/{slug}', [InterpretesController::class, 'show'])->name('interprete.show');
//     Route::get('/busqueda', [InterpretesController::class, 'busqueda'])->name('interpretes.busqueda');
// });



// Rutas para el controlador de videos
// Route::get('{interprete:slug}/videos', [VideosController::class, 'byArtista'])->name('interprete.videos');
// Route::get('{interprete:slug}/videos/{id}', [VideosController::class, 'show'])->name('interprete.video.show');
