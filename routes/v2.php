<?php

use Illuminate\Support\Facades\Route;

Route::prefix('/app/v2')->middleware('auth')->group(function () {
    Route::get('/', function () {
        return view('v2.app');
    });
});
