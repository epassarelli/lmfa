<?php

use Illuminate\Support\Facades\Route;

// RControladores del front
use App\Http\Controllers\CancionesController;
use App\Http\Controllers\DiscosController;
use App\Http\Controllers\EntrevistasController;
use App\Http\Controllers\FestivalesController;
use App\Http\Controllers\InterpretesController;
use App\Http\Controllers\MitosController;
use App\Http\Controllers\NoticiasController;
use App\Http\Controllers\PeniasController;
use App\Http\Controllers\RadiosController;
use App\Http\Controllers\RecetasController;
use App\Http\Controllers\VideosController;

use App\Http\Livewire\Backend\Noticias;
use App\Http\Livewire\Backend\Interpretes;
use App\Http\Livewire\Backend\Users;


// Rutas para el controlador de discografía
Route::get('{interprete:slug}/discografia', [DiscosController::class, 'index'])->name('interprete.discografia');
Route::get('{interprete:slug}/discografia/{id}', [DiscosController::class, 'show'])->name('interprete.album.show');

// Rutas para el controlador de noticias
Route::get('{interprete:slug}/noticias', [NoticiasController::class, 'index'])->name('interprete.noticias');
Route::get('{interprete:slug}/noticias/{id}', [NoticiasController::class, 'show'])->name('interprete.noticia.show');

// Rutas para el controlador de letras
Route::get('{interprete:slug}/letras-de-canciones', [CancionesController::class, 'index'])->name('interprete.canciones');
Route::get('{interprete:slug}/letras-de-canciones/{id}', [CancionesController::class, 'show'])->name('interprete.cancion.show');

// Rutas para el controlador de videos
Route::get('{interprete:slug}/videos', [VideosController::class, 'index'])->name('interprete.videos');
Route::get('{interprete:slug}/videos/{id}', [VideosController::class, 'show'])->name('interprete.video.show');

// Rutas para el controlador de shows
Route::get('{interprete:slug}/shows', [ShowsController::class, 'index'])->name('interprete.shows');
Route::get('{interprete:slug}/shows/{id}', [ShowsController::class, 'show'])->name('interprete.show.show');

// Rutas para el controlador de entrevistas
Route::get('{interprete:slug}/entrevistas', [EntrevistasController::class, 'index'])->name('interprete.entrevistas');
Route::get('{interprete:slug}/entrevistas/{id}', [EntrevistasController::class, 'show'])->name('interprete.entrevista.show');


// Rutas para el controlador de Festivales:
Route::get('festivales', [FestivalesController::class, 'index'])->name('festivales.index');
Route::get('festivales/{id}', [FestivalesController::class, 'show'])->name('festivales.show');

// Rutas para el controlador de Radios:
Route::get('radios', [RadiosController::class, 'index'])->name('radios.index');
Route::get('radios/{id}', [RadiosController::class, 'show'])->name('radios.show');

// Rutas para el controlador de Peñas:
Route::get('penias', [PeniasController::class, 'index'])->name('penas.index');
Route::get('penias/{id}', [PeniasController::class, 'show'])->name('penas.show');

// Rutas para el controlador de Comidas:
Route::get('comidas', [RecetasController::class, 'index'])->name('comidas.index');
Route::get('comidas/{id}', [RecetasController::class, 'show'])->name('comidas.show');

// Rutas para el controlador de Mitos:
Route::get('mitos', [MitosController::class, 'index'])->name('mitos.index');
Route::get('mitos/{id}', [MitosController::class, 'show'])->name('mitos.show');

// Rutas para el controlador de Noticias:
Route::get('noticias', [NoticiasController::class, 'index'])->name('noticias.index');
Route::get('noticias/{id}', [NoticiasController::class, 'show'])->name('noticias.show');

// Rutas para el controlador de Letras de canciones:
Route::get('letras-de-canciones', [CancionesController::class, 'index'])->name('canciones.index');
// Route::get('letras/{id}', [CancionesController::class, 'show'])->name('letras.show');

// Rutas para el controlador de Cartelera:
Route::get('cartelera', [ShowsController::class, 'index'])->name('cartelera.index');
Route::get('cartelera/{id}', [ShowsController::class, 'show'])->name('cartelera.show');

// Rutas para el controlador de Cartelera:
Route::get('videos', [VideosController::class, 'index'])->name('videos.index');

// Rutas para el controlador de interpretes:
Route::prefix('interpretes')->group(function () {
    Route::get('/{slug}', [InterpretesController::Class, 'show'])->name('interprete.show');
    Route::get('/busqueda', [InterpretesController::Class, 'busqueda'])->name('interpretes.busqueda');
    Route::get('/', [InterpretesController::Class, 'index'])->name('interpretes.index');
});

// Rutas para el controlador de noticias:
Route::prefix('noticias')->group(function () {
    Route::get('/noticias-de-{slug}', [NoticiasController::Class, 'byInterprete'])->name('noticias.byInterprete');
    Route::get('/{slug}', [NoticiasController::Class, 'show'])->name('noticias.show');
    Route::get('/busqueda', [NoticiasController::Class, 'busqueda'])->name('noticias.busqueda');
    //Route::get('/', [NoticiasController::Class, 'index'])->name('noticias.index');
});

Route::get('/', [HomeController::Class, 'index'])->name('home.index');

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified'
])->group(function () {

    Route::prefix('admin')->group(function () {
        Route::get('/usuarios', Users::class)->name('admin.usuarios');
        Route::get('/interpretes', Interpretes::class)->name('admin.interpretes');
        Route::get('/noticias', Noticias::class)->name('admin.noticias');
    });

    Route::get('/', function () {
        return view('dashboard');
    })->name('dashboard');
});
