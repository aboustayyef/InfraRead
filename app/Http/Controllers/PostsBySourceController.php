<?php

namespace App\Http\Controllers;

use App\Models\Source;

class PostsBySourceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Source $source)
    {
        return $source->getLatestPosts(40);
    }
}
