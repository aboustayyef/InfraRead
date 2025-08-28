<?php

namespace App\Jobs;

use App\Models\Post;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Client\RequestException;

/**
 * GenerateSummaryJob - Background job for generating AI summaries of posts
 *
 * This job is used when users request AI summaries via the API. Running summary
 * generation in the background provides several benefits:
 *
 * 1. **Non-blocking responses**: Users get immediate API responses instead of
 *    waiting 3-10 seconds for OpenAI to process the request
 * 2. **Better error handling**: Failed requests can be retried automatically
 * 3. **Rate limiting**: We can control how many summary requests we send to
 *    OpenAI per minute to stay within API limits
 * 4. **Caching opportunities**: We can implement intelligent caching strategies
 *
 * Teaching Note: This is a perfect example of when to use background jobs.
 * AI API calls can be slow and unreliable, so we want to:
 * - Respond quickly to users ("your summary is being generated")
 * - Handle failures gracefully with retries
 * - Control the rate of expensive external API calls
 * - Provide a better user experience overall
 */
class GenerateSummaryJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     * AI APIs can be flaky, so we want a few retries.
     */
    public int $tries = 3;

    /**
     * The maximum number of seconds the job can run.
     * OpenAI can be slow, but we don't want jobs hanging forever.
     */
    public int $timeout = 60;

    /**
     * Calculate the number of seconds to wait before retrying.
     * Uses exponential backoff to avoid hammering the API.
     */
    public function backoff(): array
    {
        return [10, 30, 120]; // 10 seconds, 30 seconds, 2 minutes
    }

    /**
     * Create a new job instance.
     *
     * We store the post we want to summarize and the number of sentences requested.
     * The job system will serialize these when queuing and deserialize when processing.
     */
    public function __construct(
        public Post $post,
        public int $sentences = 2,
        public ?string $cacheKey = null
    ) {
        // Summary generation goes to the 'summaries' queue for separate processing
        // This allows us to control the rate of AI API calls independently
        $this->onQueue('summaries');

        // Generate a cache key if none provided (for API consumers to check status)
        $this->cacheKey = $cacheKey ?? "summary_job_post_{$this->post->id}_" . time();
    }

    /**
     * Execute the job.
     *
     * This method calls the existing summary functionality and caches the result.
     * The cache key allows API consumers to check if their summary is ready.
     */
    public function handle(): void
    {
        Log::info('Starting AI summary generation job', [
            'post_id' => $this->post->id,
            'post_title' => $this->post->title,
            'sentences' => $this->sentences,
            'cache_key' => $this->cacheKey,
            'job_id' => $this->job->getJobId(),
            'attempt' => $this->attempts()
        ]);

        // Mark job as started in cache
        Cache::put($this->cacheKey . '_status', 'processing', 300); // 5 minutes

        try {
            // Use the existing summary method from the Post model
            $summary = $this->post->summary($this->sentences);

            // Check if the summary generation failed
            if (str_starts_with($summary, 'Error')) {
                throw new \Exception("AI summary generation failed: {$summary}");
            }

            // Store the successful result in cache
            $result = [
                'status' => 'completed',
                'data' => [
                    'post_id' => $this->post->id,
                    'sentences' => $this->sentences,
                    'summary' => $summary,
                    'generated_at' => now()->toISOString()
                ]
            ];

            Cache::put($this->cacheKey, $result, 1800); // Cache for 30 minutes
            Cache::forget($this->cacheKey . '_status'); // Remove processing status

            Log::info('AI summary generation completed successfully', [
                'post_id' => $this->post->id,
                'sentences' => $this->sentences,
                'cache_key' => $this->cacheKey,
                'job_id' => $this->job->getJobId(),
                'summary_length' => strlen($summary)
            ]);

        } catch (RequestException $e) {
            // HTTP request errors (timeouts, API errors, etc.)
            Log::error('AI summary generation failed with HTTP error', [
                'post_id' => $this->post->id,
                'cache_key' => $this->cacheKey,
                'error_message' => $e->getMessage(),
                'job_id' => $this->job->getJobId(),
                'attempt' => $this->attempts()
            ]);

            // Store error in cache for API consumers
            $this->cacheError("HTTP error: {$e->getMessage()}");

            // Retry for HTTP errors (network issues, API downtime, etc.)
            if ($this->attempts() < $this->tries) {
                Log::info('Will retry AI summary generation job', [
                    'post_id' => $this->post->id,
                    'attempt' => $this->attempts(),
                    'max_tries' => $this->tries
                ]);
                throw $e; // Re-throw to trigger retry
            } else {
                Log::error('AI summary generation job failed permanently due to HTTP errors', [
                    'post_id' => $this->post->id,
                    'final_attempt' => $this->attempts()
                ]);
                $this->fail($e);
            }

        } catch (\Exception $e) {
            // Other errors (API key issues, malformed responses, etc.)
            Log::error('AI summary generation failed with error', [
                'post_id' => $this->post->id,
                'cache_key' => $this->cacheKey,
                'error_message' => $e->getMessage(),
                'job_id' => $this->job->getJobId(),
                'attempt' => $this->attempts()
            ]);

            $this->cacheError($e->getMessage());

            // Some errors are worth retrying (temporary API issues)
            // Others are not (missing API key, malformed post content)
            $retryableErrors = [
                'Error summarizing', // Generic API error from Post model
                'timeout',
                'server error',
                'rate limit'
            ];

            $shouldRetry = collect($retryableErrors)->some(function ($pattern) use ($e) {
                return str_contains(strtolower($e->getMessage()), $pattern);
            });

            if ($shouldRetry && $this->attempts() < $this->tries) {
                Log::info('Will retry AI summary generation job for retryable error', [
                    'post_id' => $this->post->id,
                    'attempt' => $this->attempts(),
                    'max_tries' => $this->tries
                ]);
                throw $e; // Re-throw to trigger retry
            } else {
                Log::error('AI summary generation job failed permanently', [
                    'post_id' => $this->post->id,
                    'final_attempt' => $this->attempts(),
                    'reason' => $shouldRetry ? 'max_attempts_reached' : 'not_retryable'
                ]);
                $this->fail($e);
            }
        }
    }

    /**
     * Handle a job failure.
     *
     * This method is called when the job fails permanently (after all retries).
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('AI summary generation job failed permanently', [
            'post_id' => $this->post->id,
            'post_title' => $this->post->title,
            'sentences' => $this->sentences,
            'cache_key' => $this->cacheKey,
            'error_message' => $exception->getMessage(),
            'job_id' => $this->job?->getJobId(),
            'total_attempts' => $this->attempts()
        ]);

        // Cache the permanent failure for API consumers
        $this->cacheError("Permanent failure: {$exception->getMessage()}", true);
    }

    /**
     * Helper method to cache error information for API consumers.
     */
    private function cacheError(string $message, bool $permanent = false): void
    {
        $errorResult = [
            'status' => $permanent ? 'failed' : 'error',
            'error' => $message,
            'post_id' => $this->post->id,
            'failed_at' => now()->toISOString()
        ];

        Cache::put($this->cacheKey, $errorResult, 300); // Cache errors for 5 minutes
        Cache::forget($this->cacheKey . '_status'); // Remove processing status
    }
}
