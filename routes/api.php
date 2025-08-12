<?php

use App\Http\Controllers\UrlAnalysisController;
use App\Http\Controllers\Api\V1\PostController as V1PostController;
use App\Http\Controllers\Api\V1\SourceController as V1SourceController;
use App\Http\Controllers\Api\V1\CategoryController as V1CategoryController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
Route::get('test', function(){
return 'test successful';
});

// URL analysis for adding new sources
Route::get('urlanalyze', [UrlAnalysisController::class, 'index']);

Route::get('v2_readlaterservice', function () {
    switch (env('PREFERRED_READLATER_SERVICE')) {
        case 'pocket':
            return 'pocket';
        case 'instapaper':
            return 'instapaper';
        case 'omnivore':
            return 'omnivore';
        default:
            return 'none';
    }
});

// API V1 - Phase 1 read-only endpoints
Route::prefix('v1')->middleware('auth')->group(function () {
    Route::get('/posts', [V1PostController::class, 'index']);
    Route::get('/posts/{post}', [V1PostController::class, 'show']);
    Route::get('/sources', [V1SourceController::class, 'index']);
    Route::get('/sources/{source}', [V1SourceController::class, 'show']);
    Route::get('/categories', [V1CategoryController::class, 'index']);
    Route::get('/categories/{category}', [V1CategoryController::class, 'show']);
});
