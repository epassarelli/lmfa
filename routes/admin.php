<?php

use Illuminate\Support\Facades\Route;

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
