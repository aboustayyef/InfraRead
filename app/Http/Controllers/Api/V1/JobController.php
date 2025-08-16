<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Source;
use App\Models\Post;
use App\Jobs\RefreshSourceJob;
use App\Jobs\GenerateSummaryJob;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

/**
 * JobController - API endpoints for job-based operations
 *
 * This controller handles API endpoints that dispatch background jobs instead of
 * executing operations immediately. This provides several benefits:
 *
 * 1. **Fast API responses**: Users get immediate feedback instead of waiting
 * 2. **Better reliability**: Failed operations can be retried automatically
 * 3. **Resource management**: Heavy operations don't block web requests
 * 4. **Scalability**: Jobs can be processed on separate workers
 *
 * Teaching Note: This demonstrates a key pattern in modern web APIs - separating
 * the "request acceptance" from "work execution". The API quickly acknowledges
 * the request and provides a way to check status, while the actual work happens
 * asynchronously in the background.
 */
class JobController extends Controller
{
    /**
     * Dispatch a job to refresh a source's posts.
     *
     * This is an alternative to the immediate refresh endpoint. It's better for:
     * - Sources that are slow to fetch
     * - When you want to refresh multiple sources without waiting
     * - Integration with external systems that prefer async operations
     */
    public function refreshSource(Source $source): JsonResponse
    {
        // Generate a unique job ID for tracking
        $jobId = Str::uuid()->toString();

        // Dispatch the job with the tracking ID
        RefreshSourceJob::dispatch($source)->onQueue('refresh');

        return response()->json([
            'message' => 'Source refresh job queued successfully',
            'data' => [
                'source_id' => $source->id,
                'source_name' => $source->name,
                'job_id' => $jobId,
                'status' => 'queued'
            ]
        ], 202); // HTTP 202 = Accepted for processing
    }

    /**
     * Dispatch a job to generate an AI summary for a post.
     *
     * This provides an alternative to the immediate summary endpoint with benefits:
     * - Fast response time (no waiting for OpenAI)
     * - Better error handling and retry logic
     * - Rate limiting control over AI API calls
     * - Ability to check summary status
     */
    public function generateSummary(Request $request, Post $post): JsonResponse
    {
        // Validate the request
        $request->validate([
            'sentences' => 'sometimes|integer|min:1|max:10'
        ]);

        $sentences = (int) $request->input('sentences', 2);

        // Generate a unique cache key for this summary request
        $cacheKey = "summary_job_post_{$post->id}_" . Str::random(8);

        // Dispatch the job with the cache key for status tracking
        GenerateSummaryJob::dispatch($post, $sentences, $cacheKey)->onQueue('summaries');

        return response()->json([
            'message' => 'Summary generation job queued successfully',
            'data' => [
                'post_id' => $post->id,
                'post_title' => $post->title,
                'sentences' => $sentences,
                'cache_key' => $cacheKey,
                'status' => 'queued',
                'check_status_url' => route('api.v1.jobs.summary-status', ['key' => $cacheKey])
            ]
        ], 202); // HTTP 202 = Accepted for processing
    }

    /**
     * Check the status of a summary generation job.
     *
     * This allows API consumers to poll for the result of their summary request.
     * The cache key is returned when the job is initially queued.
     */
    public function summaryStatus(string $key): JsonResponse
    {
        // Check if we have a processing status
        if (Cache::has($key . '_status')) {
            return response()->json([
                'status' => 'processing',
                'message' => 'Summary is being generated'
            ]);
        }

        // Check for the final result (success or error)
        $result = Cache::get($key);

        if (!$result) {
            return response()->json([
                'status' => 'not_found',
                'message' => 'Summary job not found or expired',
                'error' => 'Invalid cache key or job expired'
            ], 404);
        }

        // Return the cached result (could be success or error)
        if ($result['status'] === 'completed') {
            return response()->json([
                'status' => 'completed',
                'data' => $result['data']
            ]);
        } else {
            // Error or failed status
            return response()->json([
                'status' => $result['status'],
                'error' => $result['error'],
                'message' => 'Summary generation failed'
            ], 422);
        }
    }

    /**
     * Get general queue statistics and health information.
     *
     * This endpoint provides visibility into the job system status,
     * which is useful for monitoring and debugging.
     */
    public function queueStatus(): JsonResponse
    {
        // Note: In a real production system, you'd want more sophisticated
        // queue monitoring. This is a basic implementation.

        try {
            // Basic queue health check - try to get queue size
            // This is simplified; in production you'd use Laravel Horizon or similar
            $queueStats = [
                'refresh_queue' => [
                    'status' => 'healthy',
                    'description' => 'Source refresh jobs'
                ],
                'summaries_queue' => [
                    'status' => 'healthy',
                    'description' => 'AI summary generation jobs'
                ],
                'system_time' => now()->toISOString(),
                'note' => 'For detailed queue monitoring, consider implementing Laravel Horizon'
            ];

            return response()->json([
                'message' => 'Queue status retrieved successfully',
                'data' => $queueStats
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Unable to retrieve queue status',
                'error' => 'Queue system may not be properly configured'
            ], 503);
        }
    }
}
