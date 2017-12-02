<?php

namespace App\Http\Controllers;

use App\Post;
use Illuminate\Http\Request;

class PostByReadStatusController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($readStatus = 'unread')
    {
        if ($readStatus == 'unread') {
            return Post::with(['Source','Category'])->where('read',0)->OrderBy('posted_at','desc')->take(60)->get();
        }
        return Post::with(['Source','Category'])->OrderBy('posted_at','desc')->take(60)->get();
    }

}
