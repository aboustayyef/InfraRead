<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Post;
use App\Models\Source;

class VueAdminController extends Controller
{
    /**
     * Display the admin landing page with quick stats.
     */
    public function home()
    {
        $user = auth()->user();

        return view('admin.vue-home', [
            'title' => 'Admin Home',
            'api_token' => $this->resolveApiToken(),
            'stats' => [
                'sources' => Source::count(),
                'categories' => Category::count(),
                'posts' => Post::count(),
                'unread_posts' => Post::where('read', false)->count(),
                'tokens' => $user ? $user->tokens()->count() : 0,
            ],
        ]);
    }

    /**
     * Display the Vue.js admin interface for source management.
     *
     * @return \Illuminate\Http\Response
     */
    public function sources()
    {
        return view('admin.vue-sources', [
            'title' => 'Sources Management',
            'api_token' => $this->resolveApiToken(),
        ]);
    }

    /**
     * Display the Vue.js admin interface for category management.
     *
     * @return \Illuminate\Http\Response
     */
    public function categories()
    {
        return view('admin.vue-categories', [
            'title' => 'Categories Management',
            'api_token' => $this->resolveApiToken(),
        ]);
    }

    private function resolveApiToken(): string
    {
        $token = env('INFRAREAD_API_TOKEN');

        if (!$token) {
            $user = auth()->user();
            $token = $user->createToken('admin-spa-token')->plainTextToken;
        }

        return $token;
    }
}
