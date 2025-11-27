<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    // Listar noticias (con filtros opcionales)
    Route::get('news', [\App\Http\Controllers\Api\NewsController::class, 'index']);

    // Crear noticia
    Route::post('news', [\App\Http\Controllers\Api\NewsController::class, 'store']);

    // Actualizar noticia
    Route::put('news/{news}', [\App\Http\Controllers\Api\NewsController::class, 'update']);

    // Ver noticia individual (opcional pero util)
    Route::get('news/{news}', [\App\Http\Controllers\Api\NewsController::class, 'show']);

    // Eliminar noticia (opcional)
    Route::delete('news/{news}', [\App\Http\Controllers\Api\NewsController::class, 'destroy']);

    // --- Nuevas Rutas ---

    // Album
    Route::apiResource('albums', \App\Http\Controllers\Api\AlbumController::class);

    // Cancion
    Route::apiResource('songs', \App\Http\Controllers\Api\SongController::class);

    // Comida
    Route::apiResource('foods', \App\Http\Controllers\Api\FoodController::class);

    // Festival
    Route::apiResource('festivals', \App\Http\Controllers\Api\FestivalController::class);

    // Interprete
    Route::apiResource('artists', \App\Http\Controllers\Api\ArtistController::class);

    // Mito
    Route::apiResource('myths', \App\Http\Controllers\Api\MythController::class);
});
