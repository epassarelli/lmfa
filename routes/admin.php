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

use App\Http\Controllers\Backend\CategoryController;
use App\Http\Controllers\Backend\ClassifiedController;
use App\Http\Controllers\Backend\TagController;


Route::group(
  ['middleware' => ['auth']],
  function () {

    Route::resource('roles', RoleController::class)->names('roles');
    Route::resource('users', UserController::class)->names('users');
    Route::resource('permissions', PermissionController::class)->names('permissions');
    Route::resource('categories', CategoryController::class);
    
    Route::resource('classifieds', ClassifiedController::class)->names([
        'index' => 'backend.classifieds.index',
        'create' => 'backend.classifieds.create',
        'store' => 'backend.classifieds.store',
        'show' => 'backend.classifieds.show',
        'edit' => 'backend.classifieds.edit',
        'update' => 'backend.classifieds.update',
        'destroy' => 'backend.classifieds.destroy',
    ]);
    Route::post('classifieds/{classified}/approve', [ClassifiedController::class, 'approve'])->name('backend.classifieds.approve');
    Route::post('classifieds/{classified}/reject', [ClassifiedController::class, 'reject'])->name('backend.classifieds.reject');
    
    Route::resource('tags', TagController::class);

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
    ])->parameters([
      'festivales' => 'festival'
    ]);

    Route::post('canciones/store-ajax', [CancionController::class, 'storeAjax'])->name('backend.canciones.store-ajax');
    Route::get('canciones/data', [CancionController::class, 'getCanciones'])->name('backend.canciones.get');
    Route::resource('canciones', CancionController::class)->names([
      'index' => 'backend.canciones.index',
      'create' => 'backend.canciones.create',
      'store' => 'backend.canciones.store',
      'show' => 'backend.canciones.show',
      'edit' => 'backend.canciones.edit',
      'update' => 'backend.canciones.update',
      'destroy' => 'backend.canciones.destroy',
    ])->parameters([
      'canciones' => 'cancion'
    ]);


    Route::resource('discos', AlbumController::class)->names([
      'index' => 'backend.discos.index',
      'create' => 'backend.discos.create',
      'store' => 'backend.discos.store',
      'show' => 'backend.discos.show',
      'edit' => 'backend.discos.edit',
      'update' => 'backend.discos.update',
      'destroy' => 'backend.discos.destroy',
    ])->parameters([
      'discos' => 'album'
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


    Route::resource('contributions', \App\Http\Controllers\Backend\ContributionController::class)->only(['index', 'show'])->names([
      'index' => 'backend.contributions.index',
      'show' => 'backend.contributions.show',
    ]);
    Route::post('contributions/{id}/approve', [\App\Http\Controllers\Backend\ContributionController::class, 'approve'])->name('backend.contributions.approve');
    Route::post('contributions/{id}/reject', [\App\Http\Controllers\Backend\ContributionController::class, 'reject'])->name('backend.contributions.reject');

    Route::resource('newsletter-subscribers', \App\Http\Controllers\Backend\NewsletterSubscriberController::class)->only(['index'])->names([
      'index' => 'backend.newsletter.index'
    ]);
    Route::post('newsletter-subscribers/{subscriber}/toggle', [\App\Http\Controllers\Backend\NewsletterSubscriberController::class, 'toggleStatus'])->name('backend.newsletter.toggle');

    Route::get('/', [Dashboard::class, 'index'])->name('dashboard');
  }
);
