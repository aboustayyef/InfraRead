<?php

namespace App\Http\Controllers;

use App\Source;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class RefreshPostsController extends Controller
{
    public function handle(Source $source)
    {
        $exitCode = Artisan::call('app:update_posts',['source' => $source->id]);
        return 'Successfully updated ' . $source->name;
    }
}
