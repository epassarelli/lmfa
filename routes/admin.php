<?php


use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Backend\RoleController;
use App\Http\Controllers\Backend\UserController;
use App\Http\Controllers\Backend\PermissionController;

use App\Http\Controllers\Backend\Dashboard;
use App\Http\Controllers\Backend\InterpreteController;
use App\Http\Controllers\Backend\NoticiaController;
use App\Http\Controllers\Backend\AlbumController;
use App\Http\Controllers\Backend\CancionController;
use App\Http\Controllers\Backend\ComidaController;
use App\Http\Controllers\Backend\FestivalController;
use App\Http\Controllers\Backend\MitoController;
use App\Http\Controllers\Backend\ShowController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::group(
  ['middleware' => ['auth']],
  function () {

    Route::resource('roles', RoleController::class)->names('roles');
    Route::resource('users', UserController::class)->names('users');
    Route::resource('permissions', PermissionController::class)->names('permissions');

    Route::resource('shows', ShowController::class)->names([
      'index' => 'backend.shows.index',
      'create' => 'backend.shows.create',
      'store' => 'backend.shows.store',
      'show' => 'backend.shows.show',
      'edit' => 'backend.shows.edit',
      'update' => 'backend.shows.update',
      'destroy' => 'backend.shows.destroy',
    ]);


    Route::resource('mitos', MitoController::class)->names([
      'index' => 'backend.mitos.index',
      'create' => 'backend.mitos.create',
      'store' => 'backend.mitos.store',
      'show' => 'backend.mitos.show',
      'edit' => 'backend.mitos.edit',
      'update' => 'backend.mitos.update',
      'destroy' => 'backend.mitos.destroy',
    ]);


    Route::resource('comidas', ComidaController::class)->names([
      'index' => 'backend.comidas.index',
      'create' => 'backend.comidas.create',
      'store' => 'backend.comidas.store',
      'show' => 'backend.comidas.show',
      'edit' => 'backend.comidas.edit',
      'update' => 'backend.comidas.update',
      'destroy' => 'backend.comidas.destroy',
    ]);


    Route::resource('festivales', FestivalController::class)->names([
      'index' => 'backend.festivales.index',
      'create' => 'backend.festivales.create',
      'store' => 'backend.festivales.store',
      'show' => 'backend.festivales.show',
      'edit' => 'backend.festivales.edit',
      'update' => 'backend.festivales.update',
      'destroy' => 'backend.festivales.destroy',
    ]);


    Route::resource('canciones', CancionController::class)->names([
      'index' => 'backend.canciones.index',
      'create' => 'backend.canciones.create',
      'store' => 'backend.canciones.store',
      'show' => 'backend.canciones.show',
      'edit' => 'backend.canciones.edit',
      'update' => 'backend.canciones.update',
      'destroy' => 'backend.canciones.destroy',
    ]);


    Route::resource('discos', AlbumController::class)->names([
      'index' => 'backend.albums.index',
      'create' => 'backend.albums.create',
      'store' => 'backend.albums.store',
      'show' => 'backend.albums.show',
      'edit' => 'backend.albums.edit',
      'update' => 'backend.albums.update',
      'destroy' => 'backend.albums.destroy',
    ]);


    Route::resource('noticias', NoticiaController::class)->names([
      'index' => 'backend.noticias.index',
      'create' => 'backend.noticias.create',
      'store' => 'backend.noticias.store',
      'show' => 'backend.noticias.show',
      'edit' => 'backend.noticias.edit',
      'update' => 'backend.noticias.update',
      'destroy' => 'backend.noticias.destroy',
    ]);


    Route::resource('interpretes', InterpreteController::class)->names([
      'index' => 'backend.interpretes.index',
      'create' => 'backend.interpretes.create',
      'store' => 'backend.interpretes.store',
      'show' => 'backend.interpretes.show',
      'edit' => 'backend.interpretes.edit',
      'update' => 'backend.interpretes.update',
      'destroy' => 'backend.interpretes.destroy',
    ]);


    Route::get('/', [Dashboard::class, 'index'])->name('dashboard');
  }
);
