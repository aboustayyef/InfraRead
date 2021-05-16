<?php

//Find Read Later Service

use App\Models\Post;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PostController;

Route::get('/simpleapi/readlaterservice', function () {
    switch (env('PREFERRED_READLATER_SERVICE')) {
        case 'pocket':
            return 'pocket';
            break;
        case 'instapaper':
            return 'instapaper';
    }
    return 'none';
})->middleware('auth');


// Get Posts
Route::get('/simpleapi/{which}/{details?}', function ($which, $details = null) {
    // Options are: /all
    //              /source/source_id
    //              /category/category_id

    $common_query = Post::With('Source')->with('Category')->where('read', 0);
    switch ($which) {
        case 'all':
            return  $common_query->orderBy('posted_at', 'asc')->get();
            break;
        case 'source':
            return $common_query->where('source_id', $details)->orderBy('posted_at', 'asc')->get();
        // case 'category'
        }
    abort(404);
})->middleware('auth');

// Remnant from previous api for patching read posts
Route::resource('api/posts', PostController::class)->only(['index', 'update'])->middleware('auth');
