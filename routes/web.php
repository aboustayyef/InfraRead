<?php

use App\Post;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/markallread', function(){
    Post::all()->each(function($post){
        $post->read = 1;
        $post->save();
    });
    return redirect('/');
});

Route::middleware('auth')->get('/', function(){
    return view('home');
});

// Authentication Routes...
$this->get('login', 'Auth\LoginController@showLoginForm')->name('login');
$this->post('login', 'Auth\LoginController@login');
$this->post('logout', 'Auth\LoginController@logout')->name('logout');

Route::prefix('api')->middleware('auth')->group(function(){
	Route::get('source', function(){
		return App\Source::all();
	});

    Route::get('posts/{source_id}', function($source_id){
        return Post::with(['Source','Tag'])->where('source_id', $source_id)->OrderBy('posted_at','desc')->take(10)->get();
    });
    Route::resource('posts', 'PostController');
});

Route::prefix('admin')->middleware('auth')->group(function(){
    Route::resource('source', 'AdminSourceController', ['as' => 'admin'])->except('show');
    Route::resource('tag', 'AdminTagController',['as' => 'admin'])->except('show');
    Route::get('/', 'AdminController@index');
});
