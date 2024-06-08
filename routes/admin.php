<?php

use App\Http\Controllers\Backend\Dashboard;
use App\Http\Controllers\Backend\InterpretesCrud;
use Illuminate\Support\Facades\Route;
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


Route::resource('noticias', NoticiaController::class)->names([
  'index' => 'crud.noticias.index',
  'create' => 'noticias.create',
  'store' => 'noticias.store',
  'show' => 'noticias.show',
  'edit' => 'noticias.edit',
  'update' => 'noticias.update',
  'destroy' => 'noticias.destroy',
]);

Route::resource('interpretes', InterpretesCrud::class)->names([
  'index' => 'crud.interpretes.index',
  'create' => 'interpretes.create',
  'store' => 'interpretes.store',
  'show' => 'interpretes.show',
  'edit' => 'interpretes.edit',
  'update' => 'interpretes.update',
  'destroy' => 'interpretes.destroy',
]);

Route::get('/', [Dashboard::class, 'index'])->name('dashboard');
