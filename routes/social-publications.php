<?php

use App\Http\Controllers\Api\SocialPublicationController;
use App\Http\Middleware\SocialPublicationOperator;
use Illuminate\Support\Facades\Route;

Route::prefix('v1/social-publications')->middleware(['auth:sanctum', SocialPublicationOperator::class])->group(function () {
    Route::get('candidates', [SocialPublicationController::class, 'candidates'])->middleware('throttle:60,1');
    Route::post('claims', [SocialPublicationController::class, 'claim'])->middleware('throttle:30,1');
    Route::post('{target}/begin', [SocialPublicationController::class, 'begin'])->whereNumber('target')->middleware('throttle:30,1');
    Route::post('{target}/render', [SocialPublicationController::class, 'render'])->whereNumber('target')->middleware('throttle:10,1');
    Route::post('{target}/result', [SocialPublicationController::class, 'result'])->whereNumber('target')->middleware('throttle:30,1');
    Route::get('{target}', [SocialPublicationController::class, 'show'])->whereNumber('target')->middleware('throttle:60,1');
});
