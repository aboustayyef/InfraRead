<?php

// Onboarding Routes

use Illuminate\Http\Request;
use App\Models\Source;
use App\Utilities\OpmlImporter;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    // If a user exists, but not RSS feeds is set up go to Setup Screen
    if (Source::count() == 0) {
        return redirect('/setup');
    }
    return redirect('/app');
});

Route::get('/setup', function () {
    return view('setup');
})->middleware(['auth']);

Route::post('/uploadOpml', function (Request $request) {
    $request->file('opml')->storeAs('uploaded', 'feeds.opml');
    OpmlImporter::process();

    return redirect('/admin/source');
})->middleware('auth');