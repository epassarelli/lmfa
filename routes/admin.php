<?php

use Illuminate\Support\Facades\Route;
<<<<<<< HEAD

use App\Http\Livewire\Backend\Noticias;
use App\Http\Livewire\Backend\Interpretes;
use App\Http\Livewire\Backend\Users;

Route::middleware([
  'auth:sanctum',
  config('jetstream.auth_session'),
  'verified'
])->group(function () {

  Route::prefix('admin')->group(function () {
    Route::get('/usuarios', Users::class)->name('admin.usuarios');
    Route::get('/interpretes', Interpretes::class)->name('admin.interpretes');
    Route::get('/noticias', Noticias::class)->name('admin.noticias');
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

  // Route::get('dashboard', function () {
  //   return view('dashboard');
  // })->name('dashboard');
});
=======
use App\Http\Controllers\Backend\NoticiaController;


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

Route::resource('admin/noticias', NoticiaController::class)->names([
  'index' => 'crud.noticias.index',
  'create' => 'crud.noticias.create',
  'store' => 'crud.noticias.store',
  'show' => 'crud.noticias.show',
  'edit' => 'crud.noticias.edit',
  'update' => 'crud.noticias.update',
  'destroy' => 'crud.noticias.destroy',
]);
>>>>>>> dev
