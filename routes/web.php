<?php

use App\Category;
use App\Post;
use App\Source;
use App\Utilities\OpmlImporter;
use Illuminate\Http\Request;

Route::get('/', function(){
    return redirect('/app');
});

Route::get('/setup', function(){
    return view('setup');
})->middleware('auth');

Route::get('/app', function(){
    $posts_source = '/api/posts';
    $posts_description = 'All Posts' ;
    $page = 'post list';
    return view('home')->with(compact('posts_source'))->with(compact('page'))->with(compact('posts_description'));
})->middleware('auth');

Route::get('/app/source/{id}', function($id){
    $source = Source::find($id);
    $posts_source = '/api/postsBySource/'.$id;
    $posts_description = 'Posts By '. $source->name ;
    $page = 'post list';
    return view('home')->with(compact('posts_source'))->with(compact('page'))->with(compact('posts_description'));
})->middleware('auth');

Route::get('/app/category/{id}', function($id){
    $category = Category::find($id);
    $posts_source = '/api/postsByCategory/'.$id;
    $posts_description = 'Posts In the [ '. $category->description . ' ] Category' ;
    $page = 'post list';
    return view('home')->with(compact('posts_source'))->with(compact('page'))->with(compact('posts_description'));
})->middleware('auth');

Route::get('/app/sources', function(){

    $sources = App\Source::all();
    $categories = App\Category::all();

    return view('sources')->with(compact('sources'))->with(compact('categories'));

})->middleware('auth');


// Authentication Routes...
$this->get('login', 'Auth\LoginController@showLoginForm')->name('login');
$this->post('login', 'Auth\LoginController@login');
$this->post('logout', 'Auth\LoginController@logout')->name('logout');


Route::post('/uploadOpml', function(Request $request){
    $request->file('opml')->storeAs('uploaded','feeds.opml');
    OpmlImporter::process();
    return redirect('/admin/source');
})->middleware('auth');

Route::get('/markallread', function(){
    Post::all()->each(function($post){
        $post->read = 1;
        $post->save();
    });
    return redirect('/');
});


// Administration
Route::prefix('admin')->middleware('auth')->group(function(){
    Route::resource('source', 'AdminSourceController', ['as' => 'admin'])->except('show');
    Route::resource('category', 'AdminCategoryController',['as' => 'admin'])->except('show');
    Route::get('/', 'AdminController@index');
});

// Ajax
Route::prefix('api')->middleware('auth')->group(function(){
    
    Route::resource('/posts', 'PostController')->only(['index','update']);

    // crawl for new posts /per source
    Route::get('/refresh/{source}', 'RefreshPostsController@handle');

    // Get a List of posts of a particular source
    Route::get('/postsBySource/{source}','PostsBySourceController@index');

    // Get a List of posts of a particular category
    Route::get('/postsByCategory/{category}','PostsByCategoryController@index');

    // Get a list of sources. Used for administering sources
    Route::get('source', function(){
        return App\Source::all();
    });
    Route::get('category', function(){
        return App\Category::all();
    });

});

