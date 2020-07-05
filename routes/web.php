<?php

use App\Category;
use App\Post;
use App\SDLog;
use App\Source;
use App\Utilities\OpmlImporter;
use Illuminate\Http\Request;


// API for external Clients Requests

Route::group(['prefix' => 'client'], function () {

    // Get the initial data
    Route::get('/initialData', function(Request $request){

        // Make sure Request contains username and password
        if (! Auth::attempt($request->only(['email','password']))){
            return response(collect(['response' => 401])->toArray(), 401);
        };

        // Get categories, sources, and posts;
        $categories = Category::where('id','>',0)->select(['id','description'])->get();
        $sources = Source::where('id','>',0)->select(['id','category_id','name','description'])->get();
        $posts = collect();
        foreach (Source::all() as $key => $source) {
            $posts = $posts->merge($source->latestPostsSinceEarliestUnread());
        }

        // consolidate all in a response object and return as json
        $res = collect(['response' => 200 , 'data'=>['categories'=>$categories, 'sources' => $sources, 'posts' => $posts]]);
        return $res->toArray();
    });

    // mark post read
    // toggle post read status
    // mark post unread

})->middleware('cors');

// Columns View
Route::get('/columns', function (Request $request) {
    $last_successful_crawl = getLastSuccesfulCrawl();

    $categories = Category::where('id','>',0)->select(['id','description'])->get()->toJson();
    $sources = Source::where('id','>',0)->select(['id','category_id','name','description'])->get()->toJson();

    // Get posts;
    $posts = collect();
    foreach (Source::all() as $key => $source) {
        $posts = $posts->merge($source->latestPostsSinceEarliestUnread());
    }
    $posts = $posts->toJson();
    return view('v2.home')
    ->with(compact('last_successful_crawl'))
    ->with(compact('categories'))
    ->with(compact('sources'))
    ->with(compact('posts'));
});






Route::get('/', function(){
    // If a user exists, but not RSS feeds is set up
    if (\App\Source::count() == 0) {
        return redirect('/setup');
    }
    return redirect('/app');
});

Route::get('/setup', function(){
    return view('setup');
})->middleware('auth');

Route::get('/app', function(){
    $posts_source = '/api/posts';
    $posts_description = 'All Posts' ;
    $page = 'post list';
    $last_successful_crawl = getLastSuccesfulCrawl();
    return view('home')
        ->with(compact('posts_source'))
        ->with(compact('page'))
        ->with(compact('posts_description'))
        ->with(compact('last_successful_crawl'));
})->middleware('auth');

Route::get('/app/source/{id}', function($id){
    $source = Source::findOrFail($id);
    $posts_source = '/api/postsBySource/'.$id;
    $posts_description = 'Posts By '. $source->name ;
    $page = 'post list';
    $last_successful_crawl = getLastSuccesfulCrawl();
    return view('home')
        ->with(compact('posts_source'))
        ->with(compact('page'))
        ->with(compact('posts_description'))
        ->with(compact('last_successful_crawl'));
})->middleware('auth');

Route::get('/app/category/{id}', function($id){
    $category = Category::findOrFail($id);
    $posts_source = '/api/postsByCategory/'.$id;
    $posts_description = 'Posts In the [ '. $category->description . ' ] Category' ;
    $page = 'post list';
    $last_successful_crawl = getLastSuccesfulCrawl();
    return view('home')
        ->with(compact('posts_source'))
        ->with(compact('page'))
        ->with(compact('posts_description'))
        ->with(compact('last_successful_crawl'));
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

// Saving for later 
$this->get('/app/readlater', '\App\Http\Controllers\ReadlaterController@index')->middleware('auth');
$this->get('/app/setuppocket/authorise', '\App\Http\Controllers\PocketSetupController@authorise')->middleware('auth');
$this->get('/app/setuppocket', '\App\Http\Controllers\PocketSetupController@index')->middleware('auth');

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
    return redirect('/app');
});


// Administration
Route::prefix('admin')->middleware('auth')->group(function(){
    Route::resource('source', 'AdminSourceController', ['as' => 'admin'])->except('show');
    Route::resource('category', 'AdminCategoryController',['as' => 'admin'])->except('show');
    Route::get('/', 'AdminController@index');
});

// Ajax
Route::prefix('api')->middleware('auth')->group(function(){
    
    Route::get('/postContentById/{post}', 'PostController@getContentById');
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

    // Get a list of categories. Used for administering categories
    Route::get('category', function(){
        return App\Category::all();
    });

    // analyze URL for quick add
    Route::get('urlanalyze', 'UrlAnalysisController@index');

    function getLastSuccesfulCrawl(){
        try {
            $last_crawl = new Carbon\Carbon(Storage::get('LastSuccessfulCrawl.txt'));
            // If more than 30 minutes ago, there's a problem that needs to be looked into
            if ($last_crawl->diffInMinutes() > 30) {
                return 'problem';
            }
            return $last_crawl->diffForHumans();
        } catch (\Exception $e) {
            return 'problem';
        }
    }
});

