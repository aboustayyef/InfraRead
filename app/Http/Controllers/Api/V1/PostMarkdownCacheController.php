<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Utilities\ReadLater;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class PostMarkdownCacheController extends Controller
{
    public function __invoke(Post $post): JsonResponse
    {
        try {
            // Only warm the cache when Narrator is configured
            if (config('infraread.preferred_readlater_service') !== 'narrator') {
                return response()->json([
                    'status' => 'skipped',
                    'reason' => 'Narrator read-later service is not enabled',
                ]);
            }

            $readLater = new ReadLater($post->url);
            $markdown = $readLater->warmNarratorCache($post);

            return response()->json([
                'status' => 'ok',
                'cached' => true,
                'length' => strlen($markdown),
            ]);
        } catch (\Throwable $e) {
            Log::warning('Failed to warm Narrator markdown cache', [
                'post_id' => $post->id ?? null,
                'message' => $e->getMessage(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Could not cache markdown for Narrator',
            ], 500);
        }
    }
}
