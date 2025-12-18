<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class BulkReadController extends Controller
{
    public function index()
    {
        return view('admin.bulk-read', [
            'title' => 'Mark as Read Tools',
        ]);
    }

    public function markAll(): RedirectResponse
    {
        $updated = Post::where('read', false)->update(['read' => true]);

        return back()->with('message', "Marked {$updated} posts as read.");
    }

    public function markOlderThan(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'days' => 'required|integer|min:1|max:3650',
        ]);

        $cutoff = Carbon::now()->subDays($validated['days']);
        $updated = Post::where('read', false)
            ->where('posted_at', '<=', $cutoff)
            ->update(['read' => true]);

        return back()->with('message', "Marked {$updated} posts posted on or before {$cutoff->toDateString()} as read.");
    }

    public function markExceptLatest(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'keep_latest' => 'required|integer|min:0|max:5000',
        ]);

        $keep = $validated['keep_latest'];

        if ($keep === 0) {
            return $this->markAll();
        }

        $cutoff = Post::orderBy('posted_at', 'desc')
            ->skip($keep)
            ->value('posted_at');

        if (!$cutoff) {
            return back()->with('message', 'No posts were marked. There are fewer posts than the number you want to keep.');
        }

        $updated = Post::where('read', false)
            ->where('posted_at', '<=', $cutoff)
            ->update(['read' => true]);

        return back()->with('message', "Kept the latest {$keep} posts unread. Marked {$updated} older posts as read.");
    }
}
