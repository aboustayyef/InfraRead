<?php

use App\Http\Controllers\AdminCategoryController;
use App\Http\Controllers\AdminSourceController;
use App\Http\Controllers\PocketSetupController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\PostsByCategoryController;
use App\Http\Controllers\PostsBySourceController;
use App\Http\Controllers\ReadlaterController;
use App\Http\Controllers\RefreshPostsController;
use App\Http\Controllers\UrlAnalysisController;
use App\Models\Category;
use App\Models\Post;
use App\Models\Source;
use App\Utilities\OpmlImporter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Authorisation Routes Using the Laravel Breeze package
require __DIR__.'/auth.php';

// Ajax API for app and getting posts
require __DIR__.'/infraread_api.php';

// Onboarding
require __DIR__.'/onboarding.php';

// Launch App
Route::get('/app', function () {
    $last_successful_crawl = Post::getLastSuccesfulCrawl();
    return view('home')->with('last_successful_crawl', $last_successful_crawl);
})->middleware('auth');

// Administration
Route::prefix('admin')->middleware('auth')->group(function () {
    Route::get('/', function () {
        return redirect('/admin/source');
    });
    Route::resource('source', AdminSourceController::class, ['as' => 'admin'])->except('show');
    Route::resource('category', AdminCategoryController::class, ['as' => 'admin'])->except('show');
});

// Mark all as read
Route::get('/markallread', function () {
    Post::where('read', 0)->update(array('read' => 1));
    return redirect('/app');
});

// Saving for later
Route::get('/app/readlater', [ReadlaterController::class, 'index'])->middleware('auth');
Route::get('/app/setuppocket/authorise', [PocketSetupController::class, 'authorise'])->middleware('auth');
Route::get('/app/setuppocket', [PocketSetupController::class, 'index'])->middleware('auth');

// export OPML of feeds
Route::get('/feeds.opml', function () {
    $categories = Category::with('sources')->get();
    return response()->view('opml', compact('categories'))->header('Content-Disposition', 'attachment')->header('Content-Type', 'text/xml');
});

// RSS Feeds for external readers
Route::get('/rss/{source}', function(Source $source){
    $feed_items = 50;
    return response()->view('rss',['channel' => $source, 'items'=>$source->posts($feed_items)])->header('Content-Type', 'text/xml');
});

// Obsolete/ Previous Versions

// Routes for external client apps that require access (example: column view netlify)
// I haven't tested it with this new version yet
// require __DIR__.'/clients.php';

// Route::get('/app/sources', function () {
//     $sources = Source::all();
//     $categories = Category::all();

//     return view('sources')->with(compact('sources'))->with(compact('categories'));
// })->middleware('auth');

// Route::get('/app/category/{id}', function ($id) {
//     $category = Category::findOrFail($id);
//     $posts_source = '/api/postsByCategory/'.$id;
//     $posts_description = 'Posts In the [ '.$category->description.' ] Category';
//     $page = 'post list';
//     $last_successful_crawl = Post::getLastSuccesfulCrawl();

//     return view('home')
//         ->with(compact('posts_source'))
//         ->with(compact('page'))
//         ->with(compact('posts_description'))
//         ->with(compact('last_successful_crawl'));
// })->middleware('auth');

// Columns View
// Route::get('/columns', function (Request $request) {
//     $last_successful_crawl = Post::getLastSuccesfulCrawl();

//     $categories = Category::where('id', '>', 0)->select(['id', 'description'])->get()->toJson();
//     $sources = Source::where('id', '>', 0)->select(['id', 'category_id', 'name', 'description'])->get()->toJson();

//     // Get posts;
//     $posts = collect();
//     foreach (Source::all() as $key => $source) {
//         $posts = $posts->merge($source->latestPostsSinceEarliestUnread());
//     }
//     $posts = $posts->toJson();

//     return view('v2.home')
//     ->with(compact('last_successful_crawl'))
//     ->with(compact('categories'))
//     ->with(compact('sources'))
//     ->with(compact('posts'));
// });

// Ajax
// Route::prefix('api')->middleware('auth')->group(function () {
//     Route::get('/postContentById/{post}', [PostController::class, 'getContentById']);
//     Route::resource('/posts', PostController::class)->only(['index', 'update']);

//     // crawl for new posts /per source
//     Route::get('/refresh/{source}', [RefreshPostsController::class, 'handle']);

//     // Get a List of posts of a particular source
//     Route::get('/postsBySource/{source}', [PostsBySourceController::class, 'index']);

//     // Get a List of posts of a particular category
//     Route::get('/postsByCategory/{category}', [PostsByCategoryController::class, 'index']);

//     // Get a list of sources. Used for administering sources
//     Route::get('source', function () {
//         return Source::with('Category')->get();
//     });

//     // Get a list of categories. Used for administering categories
//     Route::get('category', function () {
//         return Category::all();
//     });

//     // analyze URL for quick add

//     Route::get('urlanalyze', [UrlAnalysisController::class, 'index']);

//     });
