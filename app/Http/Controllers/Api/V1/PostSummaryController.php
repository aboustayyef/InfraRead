<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;

class PostSummaryController extends Controller
{
    /**
     * Generate a fresh summary for a post (no caching/persistence).
     */
    public function __invoke(Request $request, Post $post)
    {
        $sentences = (int) $request->input('sentences', 2);
        if ($sentences < 1 || $sentences > 10) {
            $sentences = 2; // clamp
        }

        $summary = $post->summary($sentences);

        if (str_starts_with($summary, 'Error')) {
            return response()->json([
                'error' => $summary,
            ], 502);
        }

        return response()->json([
            'data' => [
                'post_id' => $post->id,
                'sentences' => $sentences,
                'summary' => $summary,
            ],
        ]);
    }
}
