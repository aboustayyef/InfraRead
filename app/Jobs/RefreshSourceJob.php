<?php

namespace App\Jobs;

use App\Models\Source;
use App\ValueObjects\SourceUpdateResult;
use App\Exceptions\FeedProcessing\FeedProcessingException;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * RefreshSourceJob - Background job for refreshing a single RSS source
 *
 * This job is used when users manually trigger a refresh of a specific RSS source
 * via the API. It runs asynchronously to avoid blocking the HTTP response while
 * the RSS feed is being fetched and processed.
 *
 * Teaching Note: Laravel Jobs are classes that represent units of work that can be
 * executed in the background. They're particularly useful for tasks that:
 * - Take a long time (like fetching RSS feeds)
 * - Don't need to complete immediately for the user
 * - Might fail and need to be retried
 *
 * The job implements ShouldQueue which tells Laravel this should be processed
 * by a queue worker rather than immediately in the HTTP request.
 */
class RefreshSourceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     * If RSS fetching fails due to network issues, we want to retry a few times.
     */
    public int $tries = 3;

    /**
     * The maximum number of seconds the job can run before timing out.
     * RSS feeds can be slow, but we don't want jobs hanging forever.
     */
    public int $timeout = 120;

    /**
     * Calculate the number of seconds to wait before retrying the job.
     * Uses exponential backoff: 30 seconds, then 2 minutes, then 8 minutes.
     */
    public function backoff(): array
    {
        return [30, 120, 480];
    }

    /**
     * Create a new job instance.
     *
     * Laravel will automatically serialize the Source model when the job is queued,
     * and deserialize it when the job runs. This means we just need to store the
     * source we want to refresh.
     */
    public function __construct(
        public Source $source
    ) {
        // Set the queue name for organization - refresh jobs go to 'refresh' queue
        // This allows us to process different types of jobs at different rates
        $this->onQueue('refresh');
    }

    /**
     * Execute the job.
     *
     * This is the main method that runs when the queue worker processes this job.
     * It attempts to refresh the RSS source and handles any errors that occur.
     */
    public function handle(): void
    {
        Log::info('Starting manual source refresh job', [
            'source_id' => $this->source->id,
            'source_name' => $this->source->name,
            'job_id' => $this->job->getJobId(),
            'attempt' => $this->attempts()
        ]);

        try {
            // Use the existing updatePosts method - no need to duplicate logic
            $result = $this->source->updatePosts();

            if ($result->isSuccess()) {
                Log::info('Manual source refresh completed successfully', [
                    'source_id' => $this->source->id,
                    'posts_processed' => $result->postsProcessed,
                    'duration_seconds' => $result->durationSeconds,
                    'job_id' => $this->job->getJobId()
                ]);
            } else {
                // This shouldn't happen with current implementation, but good to be defensive
                Log::warning('Manual source refresh completed with issues', [
                    'source_id' => $this->source->id,
                    'result' => $result->toArray(),
                    'job_id' => $this->job->getJobId()
                ]);
            }

        } catch (FeedProcessingException $e) {
            // Feed processing errors (HTTP errors, XML parsing, etc.)
            Log::error('Manual source refresh failed with feed processing error', [
                'source_id' => $this->source->id,
                'error_type' => $e->getErrorType(),
                'error_message' => $e->getMessage(),
                'context' => $e->getContext(),
                'job_id' => $this->job->getJobId(),
                'attempt' => $this->attempts()
            ]);

            // Determine if we should retry based on the error type
            if ($e->isRetryable() && $this->attempts() < $this->tries) {
                Log::info('Will retry manual source refresh job', [
                    'source_id' => $this->source->id,
                    'attempt' => $this->attempts(),
                    'max_tries' => $this->tries
                ]);
                throw $e; // Re-throw to trigger retry
            } else {
                // Don't retry - either not retryable or max attempts reached
                Log::error('Manual source refresh job failed permanently', [
                    'source_id' => $this->source->id,
                    'final_attempt' => $this->attempts(),
                    'reason' => $e->isRetryable() ? 'max_attempts_reached' : 'not_retryable'
                ]);
                $this->fail($e);
            }

        } catch (\Exception $e) {
            // Unexpected errors
            Log::error('Manual source refresh job failed with unexpected error', [
                'source_id' => $this->source->id,
                'error_message' => $e->getMessage(),
                'job_id' => $this->job->getJobId(),
                'attempt' => $this->attempts()
            ]);

            // For unexpected errors, always retry up to the limit
            if ($this->attempts() < $this->tries) {
                throw $e; // Re-throw to trigger retry
            } else {
                $this->fail($e);
            }
        }
    }

    /**
     * Handle a job failure.
     *
     * This method is called when the job fails permanently (after all retries).
     * We use it to ensure proper logging and cleanup.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Manual source refresh job failed permanently', [
            'source_id' => $this->source->id,
            'source_name' => $this->source->name,
            'error_message' => $exception->getMessage(),
            'job_id' => $this->job?->getJobId(),
            'total_attempts' => $this->attempts()
        ]);

        // Could potentially notify administrators or update source status here
        // For now, the Source model's error handling in updatePosts() will
        // have already updated the source's health metrics
    }
}
