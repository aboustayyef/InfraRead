<?php

namespace App\Http\Controllers;

use App\Models\Category;

class PostsByCategoryController extends Controller
{
    public function index(Category $category)
    {
        return $category->getLatestPosts(40);
    }
}
