<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Admin\Dashboard;
use App\Livewire\Admin\Users;
use App\Livewire\Admin\Noticias;
use App\Livewire\Admin\Interpretes;

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


Route::get('/dashboard', Dashboard::class)->name('admin.dashboard');
Route::get('/usuarios', Users::class)->name('admin.usuarios');
Route::get('/interpretes', Interpretes::class)->name('admin.interpretes');
Route::get('/noticias', Noticias::class)->name('admin.noticias');
