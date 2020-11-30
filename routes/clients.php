<?php

// Infraread API for external Clients Requests

use App\Models\Category;
use App\Models\Post;
use App\Models\Source;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'client'], function () {
    // Get the initial data

    Route::get('/initialData', function (Request $request) {
        // Make sure Request contains username and password
        if (!Auth::attempt($request->only(['email', 'password']))) {
            return response(collect(['response' => 401])->toArray(), 401);
        }

        // Get categories, sources, and posts;
        $categories = Category::where('id', '>', 0)->select(['id', 'description'])->get();
        $sources = Source::where('id', '>', 0)->select(['id', 'category_id', 'name', 'description'])->get();
        $posts = collect();
        foreach (Source::all() as $key => $source) {
            $posts = $posts->merge($source->latestPostsSinceEarliestUnread());
        }

        // consolidate all in a response object and return as json
        $res = collect([
            'response' => 200,
            'data' => [
                'categories' => $categories,
                'sources' => $sources,
                'posts' => $posts, ],
                ]);

        return $res->toArray();
    })->middleware('cors');

    Route::get('/toggleReadStatus', function (Request $request) {
        // Make sure Request contains username and password
        if (!Auth::attempt($request->only(['email', 'password']))) {
            return response(collect(['response' => 401])->toArray(), 401);
        }
        try {
            $post = Post::findOrFail($request->post);
            $post->read = abs(1 - $post->read);
            $post->save();

            return response(collect(['response' => 200])->toArray(), 200);            //code...
        } catch (\Throwable $th) {
            return response(collect(['response' => 500])->toArray(), 500);
        }
    })->middleware('cors');

    // mark post read
    // toggle post read status
    // mark post unread
});
