<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\InterpretesController;

use App\Http\Livewire\Backend\Noticias;
use App\Http\Livewire\Backend\Interpretes;
use App\Http\Livewire\Backend\Users;

Route::prefix('interpretes')->group(function () {
    Route::get('/', [InterpretesController::Class, 'index'])->name('interpretes.index');
    Route::get('{slug}', 'InterpretesController@show')->name('interpretes.show');
});


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
