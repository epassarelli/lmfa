<?php

use Illuminate\Support\Facades\Route;
use App\Http\Livewire\Admin\Dashboard;
// use App\Http\Livewire\Admin\Roles;
use App\Http\Livewire\Admin\Users;
use App\Http\Livewire\Admin\Noticias;
use App\Http\Livewire\Admin\Interpretes;


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
// Route::get('/roles', Roles::class)->name('admin.roles');
Route::get('/users', Users::class)->name('admin.users');
//     Route::get('/usuarios', Users::class)->name('admin.usuarios');
Route::get('/interpretes', Interpretes::class)->name('admin.interpretes');
Route::get('/noticias', Noticias::class)->name('admin.noticias');
