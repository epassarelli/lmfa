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

    // Rutas de solo lectura — accesibles a cualquier token Sanctum válido
    Route::get('news', [\App\Http\Controllers\Api\NewsController::class, 'index']);
    Route::get('news/{news}', [\App\Http\Controllers\Api\NewsController::class, 'show']);
    Route::get('knowledge-articles', [\App\Http\Controllers\Api\KnowledgeArticleController::class, 'index']);
    Route::get('knowledge-articles/{knowledge_article}', [\App\Http\Controllers\Api\KnowledgeArticleController::class, 'show']);
    Route::get('knowledge-categories', [\App\Http\Controllers\Api\KnowledgeArticleController::class, 'categories']);
    Route::apiResource('albums', \App\Http\Controllers\Api\AlbumController::class)->only(['index', 'show']);
    Route::apiResource('songs', \App\Http\Controllers\Api\SongController::class)->only(['index', 'show']);
    Route::apiResource('foods', \App\Http\Controllers\Api\FoodController::class)->only(['index', 'show']);
    Route::apiResource('festivals', \App\Http\Controllers\Api\FestivalController::class)->only(['index', 'show']);
    Route::apiResource('artists', \App\Http\Controllers\Api\ArtistController::class)->only(['index', 'show']);
    Route::apiResource('myths', \App\Http\Controllers\Api\MythController::class)->only(['index', 'show']);
    Route::apiResource('events', \App\Http\Controllers\Api\EventController::class)->only(['index', 'show']);
    Route::get('editorial-catalogs/provinces', [\App\Http\Controllers\Api\EditorialCatalogController::class, 'provinces']);
    Route::get('editorial-catalogs/localities', [\App\Http\Controllers\Api\EditorialCatalogController::class, 'localities']);
    Route::get('editorial-catalogs/months', [\App\Http\Controllers\Api\EditorialCatalogController::class, 'months']);

    // Rutas de escritura — solo administradores
    Route::middleware('role:administrador')->group(function () {
        Route::post('news', [\App\Http\Controllers\Api\NewsController::class, 'store']);
        Route::put('news/{news}', [\App\Http\Controllers\Api\NewsController::class, 'update']);
        Route::delete('news/{news}', [\App\Http\Controllers\Api\NewsController::class, 'destroy']);
        Route::post('knowledge-articles', [\App\Http\Controllers\Api\KnowledgeArticleController::class, 'store']);
        Route::put('knowledge-articles/{knowledge_article}', [\App\Http\Controllers\Api\KnowledgeArticleController::class, 'update']);
        Route::patch('knowledge-articles/{knowledge_article}', [\App\Http\Controllers\Api\KnowledgeArticleController::class, 'update']);
        Route::delete('knowledge-articles/{knowledge_article}', [\App\Http\Controllers\Api\KnowledgeArticleController::class, 'destroy']);
        Route::post('knowledge-articles/{knowledge_article}/publish', [\App\Http\Controllers\Api\KnowledgeArticleController::class, 'publish']);
        Route::post('knowledge-articles/{knowledge_article}/unpublish', [\App\Http\Controllers\Api\KnowledgeArticleController::class, 'unpublish']);
        Route::apiResource('albums', \App\Http\Controllers\Api\AlbumController::class)->except(['index', 'show']);
        Route::apiResource('songs', \App\Http\Controllers\Api\SongController::class)->except(['index', 'show']);
        Route::apiResource('foods', \App\Http\Controllers\Api\FoodController::class)->except(['index', 'show']);
        Route::apiResource('festivals', \App\Http\Controllers\Api\FestivalController::class)->except(['index', 'show']);
        Route::apiResource('artists', \App\Http\Controllers\Api\ArtistController::class)->except(['index', 'show']);
        Route::apiResource('myths', \App\Http\Controllers\Api\MythController::class)->except(['index', 'show']);
        Route::apiResource('events', \App\Http\Controllers\Api\EventController::class)->except(['index', 'show']);
    });
});
