<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PostQuoteExplanationController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, Post $post): JsonResponse
    {
        $validated = $request->validate([
            'quote' => ['required', 'string', 'max:12000'],
        ]);

        $quote = $post->normalizeQuoteText($validated['quote']);

        if (Str::wordCount($quote) < 75) {
            return response()->json([
                'message' => 'Quote must contain at least 75 words.',
                'errors' => [
                    'quote' => ['Quote must contain at least 75 words.'],
                ],
            ], 422);
        }

        $result = $post->cachedQuoteExplanation($quote);

        if (str_starts_with($result['explanation'], 'Error')) {
            return response()->json([
                'error' => $result['explanation'],
            ], 502);
        }

        return response()->json([
            'data' => [
                'post_id' => $post->id,
                'hash' => $result['hash'],
                'cached' => $result['cached'],
                'explanation' => $result['explanation'],
            ],
        ]);
    }
}
