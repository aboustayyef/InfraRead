<?php

use App\Http\Controllers\UrlAnalysisController;
use App\Http\Controllers\Api\V1\PostController as V1PostController;
use App\Http\Controllers\Api\V1\SourceController as V1SourceController;
use App\Http\Controllers\Api\V1\CategoryController as V1CategoryController;
use App\Http\Controllers\Api\V1\PostSummaryController as V1PostSummaryController;
use App\Http\Controllers\Api\V1\PostReadStatusController as V1PostReadStatusController;
use App\Http\Controllers\Api\V1\BulkPostReadStatusController as V1BulkPostReadStatusController;
use App\Http\Controllers\Api\V1\MarkAllReadController as V1MarkAllReadController;
use App\Http\Controllers\Api\V1\SourceManagementController as V1SourceManagementController;
use App\Http\Controllers\Api\V1\OpmlController as V1OpmlController;
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

// @deprecated - This endpoint will be removed. Use POST /api/v1/sources instead.
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

// API V1 - sanctum protected
Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    // Read endpoints
    Route::get('/posts', [V1PostController::class, 'index']);
    Route::get('/posts/{post}', [V1PostController::class, 'show']);
    Route::get('/sources', [V1SourceController::class, 'index']);
    Route::get('/sources/{source}', [V1SourceController::class, 'show']);
    Route::get('/categories', [V1CategoryController::class, 'index']);
    Route::get('/categories/{category}', [V1CategoryController::class, 'show']);

    // Mutation endpoints (Phase 2)
    Route::patch('/posts/{post}/read-status', [V1PostReadStatusController::class, 'update']);
    Route::patch('/posts/bulk-read-status', [V1BulkPostReadStatusController::class, 'update']);
    Route::patch('/posts/mark-all-read', [V1MarkAllReadController::class, 'markAll']);

    // Source management endpoints (Phase 3)
    Route::post('/sources', [V1SourceManagementController::class, 'store']);
    Route::put('/sources/{source}', [V1SourceManagementController::class, 'update']);
    Route::delete('/sources/{source}', [V1SourceManagementController::class, 'destroy']);
    Route::post('/sources/{source}/refresh', [V1SourceManagementController::class, 'refresh']);

    // Category management endpoints (Phase 3)
    Route::post('/categories', [V1CategoryController::class, 'store']);
    Route::put('/categories/{category}', [V1CategoryController::class, 'update']);
    Route::delete('/categories/{category}', [V1CategoryController::class, 'destroy']);

    // OPML Import/Export endpoints (Phase 3)
    Route::get('/export-opml', [V1OpmlController::class, 'export']);
    Route::post('/preview-opml', [V1OpmlController::class, 'preview']);
    Route::post('/import-opml', [V1OpmlController::class, 'import']);

    // Special endpoints
    Route::post('/posts/{post}/summary', V1PostSummaryController::class)->middleware('throttle:summaries');
});
