<?php

use App\Http\Controllers\AdminCategoryController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminSourceController;
use App\Http\Controllers\PocketSetupController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\PostsByCategoryController;
use App\Http\Controllers\PostsBySourceController;
use App\Http\Controllers\ReadlaterController;
use App\Http\Controllers\RefreshPostsController;
use App\Http\Controllers\UrlAnalysisController;
use App\Models\Category;
use App\Models\Source;
use App\Utilities\OpmlImporter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

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

// Authorisation Routes
// Using the package Laravel Breeze
require __DIR__.'/auth.php';

// Routes for external client apps that require access (example: column view netlify)
// I haven't tested it with this new version yet
require __DIR__.'/clients.php';

Route::get('/', function () {
    // If a user exists, but not RSS feeds is set up
    if (Source::count() == 0) {
        return redirect('/setup');
    }

    return redirect('/app');
});

Route::get('/app', function () {
    $posts_source = '/api/posts';
    $posts_description = 'All Posts';
    $page = 'post list';
    $last_successful_crawl = getLastSuccesfulCrawl();

    return view('home')
        ->with(compact('posts_source'))
        ->with(compact('page'))
        ->with(compact('posts_description'))
        ->with(compact('last_successful_crawl'));
})->middleware('auth');

Route::get('/app/source/{id}', function ($id) {
    $source = Source::findOrFail($id);
    $posts_source = '/api/postsBySource/'.$id;
    $posts_description = 'Posts By '.$source->name;
    $page = 'post list';
    $last_successful_crawl = getLastSuccesfulCrawl();

    return view('home')
        ->with(compact('posts_source'))
        ->with(compact('page'))
        ->with(compact('posts_description'))
        ->with(compact('last_successful_crawl'));
})->middleware('auth');

Route::get('/app/sources', function () {
    $sources = Source::all();
    $categories = Category::all();

    return view('sources')->with(compact('sources'))->with(compact('categories'));
})->middleware('auth');

Route::get('/app/category/{id}', function ($id) {
    $category = Category::findOrFail($id);
    $posts_source = '/api/postsByCategory/'.$id;
    $posts_description = 'Posts In the [ '.$category->description.' ] Category';
    $page = 'post list';
    $last_successful_crawl = getLastSuccesfulCrawl();

    return view('home')
        ->with(compact('posts_source'))
        ->with(compact('page'))
        ->with(compact('posts_description'))
        ->with(compact('last_successful_crawl'));
})->middleware('auth');

Route::get('/setup', function () {
    return view('setup');
})->middleware(['auth']);

Route::post('/uploadOpml', function (Request $request) {
    $request->file('opml')->storeAs('uploaded', 'feeds.opml');
    OpmlImporter::process();

    return redirect('/admin/source');
})->middleware('auth');

// Administration
Route::prefix('admin')->middleware('auth')->group(function () {
    Route::resource('source', AdminSourceController::class, ['as' => 'admin'])->except('show');
    Route::resource('category', AdminCategoryController::class, ['as' => 'admin'])->except('show');
    Route::get('/', [AdminController::class, 'index']);
});

// Ajax
Route::prefix('api')->middleware('auth')->group(function () {
    Route::get('/postContentById/{post}', [PostController::class, 'getContentById']);
    Route::resource('/posts', PostController::class)->only(['index', 'update']);

    // crawl for new posts /per source
    Route::get('/refresh/{source}', [RefreshPostsController::class, 'handle']);

    // Get a List of posts of a particular source
    Route::get('/postsBySource/{source}', [PostsBySourceController::class, 'index']);

    // Get a List of posts of a particular category
    Route::get('/postsByCategory/{category}', [PostsByCategoryController::class, 'index']);

    // Get a list of sources. Used for administering sources
    Route::get('source', function () {
        return Source::with('Category')->get();
    });

    // Get a list of categories. Used for administering categories
    Route::get('category', function () {
        return Category::all();
    });

    // analyze URL for quick add

    Route::get('urlanalyze', [UrlAnalysisController::class, 'index']);

    function getLastSuccesfulCrawl()
    {
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

// Saving for later
Route::get('/app/readlater', [ReadlaterController::class, 'index'])->middleware('auth');
Route::get('/app/setuppocket/authorise', [PocketSetupController::class, 'authorise'])->middleware('auth');
Route::get('/app/setuppocket', [PocketSetupController::class, 'index'])->middleware('auth');

// export OPML of feeds
Route::get('/feeds.opml', function () {
    $categories = Category::with('sources')->get();

    return response()->view('opml', compact('categories'))->header('Content-Disposition', 'attachment')->header('Content-Type', 'text/xml');
});

// Columns View
Route::get('/columns', function (Request $request) {
    $last_successful_crawl = getLastSuccesfulCrawl();

    $categories = Category::where('id', '>', 0)->select(['id', 'description'])->get()->toJson();
    $sources = Source::where('id', '>', 0)->select(['id', 'category_id', 'name', 'description'])->get()->toJson();

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

// Mark all as read (not tested for this v yet)
Route::get('/markallread', function () {
    Post::all()->each(function ($post) {
        $post->read = 1;
        $post->save();
    });

    return redirect('/app');
});
