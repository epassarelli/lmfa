<?php

use App\Http\Controllers\Api\SocialPublicationController;
use App\Http\Middleware\SocialPublicationOperator;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\InstagramPublicationController;

Route::get('v1/social-publication-assets/{hash}.jpg', function (string $hash) {
    abort_unless(config('social_publication.enabled') && config('social_publication.instagram_enabled'), 404);
    $path = \Illuminate\Support\Facades\Storage::disk('local')->path('social-publications/assets/'.$hash.'.jpg');
    // Only immutable JPEGs produced by the approved renderer, never arbitrary private storage.
    abort_unless(is_file($path) && hash_equals($hash, hash_file('sha256', $path)), 404);
    return response()->file($path, ['Content-Type' => 'image/jpeg', 'Cache-Control' => 'public, max-age=86400', 'X-Content-Type-Options' => 'nosniff', 'X-Robots-Tag' => 'noindex']);
})->where('hash', '[a-f0-9]{64}')->middleware('throttle:120,1')->name('social-publications.asset');

Route::prefix('v1/social-publications/instagram')->middleware(['auth:sanctum', SocialPublicationOperator::class, 'throttle:30,1'])->group(function () {
    Route::get('candidates', [InstagramPublicationController::class, 'candidates']);
    Route::post('claims', [InstagramPublicationController::class, 'claim']);
    Route::post('{target}/render', [InstagramPublicationController::class, 'render'])->whereNumber('target');
    Route::post('{target}/begin', [InstagramPublicationController::class, 'begin'])->whereNumber('target');
    Route::post('{target}/container', [InstagramPublicationController::class, 'container'])->whereNumber('target');
    Route::post('{target}/result', [InstagramPublicationController::class, 'result'])->whereNumber('target')->name('social-publications.instagram.result');
    Route::get('{target}', [InstagramPublicationController::class, 'show'])->whereNumber('target')->name('social-publications.instagram.show');
});

Route::prefix('v1/social-publications')->middleware(['auth:sanctum', SocialPublicationOperator::class])->group(function () {
    Route::get('candidates', [SocialPublicationController::class, 'candidates'])->middleware('throttle:60,1');
    Route::post('claims', [SocialPublicationController::class, 'claim'])->middleware('throttle:30,1');
    Route::post('{target}/begin', [SocialPublicationController::class, 'begin'])->whereNumber('target')->middleware('throttle:30,1');
    Route::post('{target}/render', [SocialPublicationController::class, 'render'])->whereNumber('target')->middleware('throttle:10,1');
    Route::post('{target}/result', [SocialPublicationController::class, 'result'])->whereNumber('target')->middleware('throttle:30,1');
    Route::get('{target}', [SocialPublicationController::class, 'show'])->whereNumber('target')->middleware('throttle:60,1');
});
