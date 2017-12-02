<?php

namespace App\Http\Controllers;

use App\Category;
use Illuminate\Http\Request;

class PostsByCategoryController extends Controller
{
    public function index(Category $category)
    {
        return $category->getLatestPosts(40);
    }
}
