<?php

use App\Post;

Route::middleware('auth')->get('/', function(){
    return view('home');
});

Route::get('/markallread', function(){
    Post::all()->each(function($post){
        $post->read = 1;
        $post->save();
    });
    return redirect('/');
});


// Authentication Routes...
$this->get('login', 'Auth\LoginController@showLoginForm')->name('login');
$this->post('login', 'Auth\LoginController@login');
$this->post('logout', 'Auth\LoginController@logout')->name('logout');

// Administration
Route::prefix('admin')->middleware('auth')->group(function(){
    Route::resource('source', 'AdminSourceController', ['as' => 'admin'])->except('show');
    Route::resource('tag', 'AdminTagController',['as' => 'admin'])->except('show');
    Route::get('/', 'AdminController@index');
});

// Ajax
Route::prefix('api')->middleware('auth')->group(function(){
    
    // Get a List of posts via XHR to display in app
    Route::resource('/posts', 'PostController');

    // Get a List of posts of a particular source
    Route::resource('/sourcePosts/{source}','SourcePostController')->only(['index']);

    // Get a list of sources. Used for administering sources
    Route::get('source', function(){
        return App\Source::all();
    });

});

