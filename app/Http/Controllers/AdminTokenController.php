<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminTokenController extends Controller
{
    public function show(Request $request): View
    {
        $user = $request->user();
        $configuredToken = config('infraread.api_token');
        $tokens = $user ? $user->tokens()->orderByDesc('created_at')->get() : collect();

        return view('admin.token', [
            'configured_token' => $configuredToken,
            'tokens' => $tokens,
        ]);
    }

    public function store(Request $request)
    {
        $user = $request->user();
        $name = $request->input('name', 'dev');
        // Optionally allow regenerating (delete old with same name)
        $user->tokens()->where('name', $name)->delete();
        $token = $user->createToken($name)->plainTextToken; // plainTextToken only visible now
        return back()->with('generated_token', $token)->with('token_name', $name);
    }

    public function destroy(Request $request)
    {
        $user = $request->user();
        $name = $request->input('name');
        if ($name) {
            $user->tokens()->where('name', $name)->delete();
            return back()->with('revoked', $name);
        }
        return back();
    }
}
