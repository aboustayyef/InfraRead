<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\PostResource;
use App\Models\Post;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function index(Request $request)
    {
        $query = Post::query();
        $includes = collect(explode(',', (string) $request->query('include')))
            ->map(fn($s) => trim($s))
            ->filter();
        $allowedIncludes = ['source', 'category'];
        $relations = $includes->intersect($allowedIncludes)->all();
        if ($relations) {
            $query->with($relations);
        }
        $filters = $request->query('filter', []);
        if (isset($filters['read'])) {
            $query->where('read', (int) $filters['read']);
        }
        if (isset($filters['source'])) {
            $query->where('source_id', (int) $filters['source']);
        }
        if (isset($filters['category'])) {
            $query->where('category_id', (int) $filters['category']);
        }
        $sort = $request->query('sort', 'posted_at');
        if ($sort === 'posted_at') {
            $query->orderBy('posted_at', 'asc');
        } else {
            $query->orderBy('posted_at', 'desc');
        }
        $size = min((int) $request->query('page.size', 50), 200);
        $posts = $query->paginate($size)->appends($request->query());
        return PostResource::collection($posts)->additional([
            'meta' => [
                'total' => $posts->total(),
                'current_page' => $posts->currentPage(),
                'last_page' => $posts->lastPage(),
                'per_page' => $posts->perPage(),
            ],
        ]);
    }

    public function show(Request $request, Post $post)
    {
        $includes = collect(explode(',', (string) $request->query('include')))
            ->map(fn($s) => trim($s))
            ->filter();
        $allowedIncludes = ['source', 'category'];
        $relations = $includes->intersect($allowedIncludes)->all();
        if ($relations) {
            $post->load($relations);
        }
        return new PostResource($post);
    }
}
