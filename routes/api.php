<?php

use App\Http\Controllers\UrlAnalysisController;
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
