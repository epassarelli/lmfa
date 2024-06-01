<?php

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

Route::resource('admin/noticias', NoticiaController::class)->names([
  'index' => 'crud.noticias.index',
  'create' => 'crud.noticias.create',
  'store' => 'crud.noticias.store',
  'show' => 'crud.noticias.show',
  'edit' => 'crud.noticias.edit',
  'update' => 'crud.noticias.update',
  'destroy' => 'crud.noticias.destroy',
]);
