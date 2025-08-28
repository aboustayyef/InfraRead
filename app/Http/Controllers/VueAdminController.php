<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class VueAdminController extends Controller
{
    /**
     * Display the Vue.js admin interface for source management.
     *
     * @return \Illuminate\Http\Response
     */
    public function sources()
    {
        // Use token from .env if available, otherwise generate a new one
        $token = env('INFRAREAD_API_TOKEN');

        if (!$token) {
            // Fallback: Generate API token for the current user
            $user = auth()->user();
            $token = $user->createToken('admin-spa-token')->plainTextToken;
        }

        return view('admin.vue-sources', [
            'title' => 'Sources Management',
            'api_token' => $token
        ]);
    }

    /**
     * Display the Vue.js admin interface for category management.
     *
     * @return \Illuminate\Http\Response
     */
    public function categories()
    {
        // Use token from .env if available, otherwise generate a new one
        $token = env('INFRAREAD_API_TOKEN');

        if (!$token) {
            // Fallback: Generate API token for the current user
            $user = auth()->user();
            $token = $user->createToken('admin-spa-token')->plainTextToken;
        }

        return view('admin.vue-categories', [
            'title' => 'Categories Management',
            'api_token' => $token
        ]);
    }
}
