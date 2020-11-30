<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $oldestUnreadPost = Post::getOldestUnreadPost();
        // dd($oldestUnreadPost);
        return
        Post::with(['Source', 'Category'])
            ->Select('id', 'title', 'url', 'excerpt', 'posted_at', 'source_id', 'category_id', 'read', 'uid', 'author')
            ->Where('posted_at', '>', $oldestUnreadPost->posted_at)
            ->OrderBy('posted_at', 'desc')
            ->get();
    }

    public function getContentById(Post $post)
    {
        return $post;
    }

    /**
     * Update the specified resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Post $post)
    {
        $post->update($request->all());
    }
}
