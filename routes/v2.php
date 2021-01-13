<?php

use Illuminate\Support\Facades\Route;

// Todo: Restore middleware auth (just uncomment the part below)

// Route::prefix('/app/v2')->middleware('auth')->group(function () {
//     Route::get('/', function () {
//         return view('v2.app');
//     });
// });

Route::prefix('/app/v2')->group(function () {
    Route::get('/', function () {
        return view('v2.app');
    });
});
