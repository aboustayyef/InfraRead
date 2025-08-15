<?php

namespace App\Models;

use App\Fetchers\rssFetcher;
use App\ValueObjects\SourceUpdateResult;
use App\Exceptions\FeedProcessing\FeedProcessingException;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class Source extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    /**
     * The attributes that should be cast to native types.
     */
    protected $casts = [
        'last_fetched_at' => 'datetime',
        'last_error_at' => 'datetime',
        'consecutive_failures' => 'integer',
        'last_fetch_duration_ms' => 'integer',
        'active' => 'boolean'
    ];

    /**
     * Define possible status values as constants for better type safety.
     */
    public const STATUS_ACTIVE = 'active';
    public const STATUS_WARNING = 'warning';
    public const STATUS_FAILED = 'failed';

    // this method has been replaced by the posts method below
    // remove later if everything is working fine
    public function posts_before_optimization($howmany = null)
    {
        $q =  $this->hasMany('App\Models\Post');
        if ($howmany) {
            return $q->latest()->take($howmany)->get();
        }
        return $q;
    }

    public function posts($howmany = null)
    {
        $key = "source_{$this->id}_posts_" . ($howmany ?: 'all');

        return Cache::remember($key, now()->addMinutes(10), function () use ($howmany) {
            $query = $this->hasMany('App\\Models\\Post')->latest();

            if ($howmany) {
                return $query->limit($howmany)->get();
            }

            return $query->get();
        });
    }

    public function media()
    {
        return $this->hasMany('App\Models\Media');
    }

    public function category()
    {
        return $this->belongsTo('App\Models\Category');
    }

    public function createAvatar($img)
    {
        $avatar = Media::createFromImage($img, 'source');
        $avatar->source_id = $this->id;
        $avatar->save();
    }

    public function hasAvatar()
    {
        // code...
    }

    public function avatar()
    {
        if (!$this->hasAvatar()) {
            return null;
        }
    }

    public static function getByNickname($nickname)
    {
        try {
            return static::where('nickname', $nickname)->first();
        } catch (\Exception $e) {
            return null;
        }
    }

    public function daysSinceLastPost()
    {
        return $this->posts->last()->posted_at->diffInDays(new Carbon());
    }

    /**
     * Update posts for this source with comprehensive error handling and performance tracking.
     *
     * This is the main method for fetching and processing new posts from RSS feeds.
     * It includes detailed error handling, performance monitoring, and status tracking.
     *
     * @return SourceUpdateResult Object containing success/failure details and metrics
     */
    public function updatePosts(): SourceUpdateResult
    {
        $startTime = microtime(true);

        try {
            // Log the start of processing
            Log::info('Starting feed processing', [
                'source_id' => $this->id,
                'source_name' => $this->name,
                'url' => $this->fetcher_source
            ]);

            // Fetch new posts from the RSS feed
            $posts = $this->fetchNewPosts();

            if ($posts->count() === 0) {
                // No new posts is a successful operation, just with 0 results
                $result = SourceUpdateResult::success(
                    postsProcessed: 0,
                    durationSeconds: microtime(true) - $startTime,
                    context: ['message' => 'No new posts available']
                );

                $this->recordSuccessfulUpdate($startTime, 0);
                return $result;
            }

            // Process the new posts (save, apply plugins, etc.)
            $processedCount = $this->processNewPosts($posts);

            // Record successful update and return result
            $result = SourceUpdateResult::success(
                postsProcessed: $processedCount,
                durationSeconds: microtime(true) - $startTime
            );

            $this->recordSuccessfulUpdate($startTime, $processedCount);

            Log::info('Feed processing completed successfully', [
                'source_id' => $this->id,
                'posts_processed' => $processedCount,
                'duration_seconds' => $result->durationSeconds
            ]);

            return $result;

        } catch (FeedProcessingException $e) {
            // Handle known feed processing errors
            $result = SourceUpdateResult::failure(
                durationSeconds: microtime(true) - $startTime,
                errorMessage: $e->getMessage(),
                errorType: $e->getErrorType(),
                context: $e->getContext()
            );

            $this->recordFailedUpdate($startTime, $e);

            Log::error('Feed processing failed', [
                'source_id' => $this->id,
                'error_type' => $e->getErrorType(),
                'error_message' => $e->getMessage(),
                'context' => $e->getContext()
            ]);

            // Re-throw for higher-level handling if needed
            throw $e;

        } catch (\Exception $e) {
            // Handle unexpected errors
            $result = SourceUpdateResult::failure(
                durationSeconds: microtime(true) - $startTime,
                errorMessage: $e->getMessage(),
                errorType: 'unexpected_error'
            );

            $this->recordUnexpectedError($startTime, $e);

            Log::error('Unexpected error during feed processing', [
                'source_id' => $this->id,
                'error_message' => $e->getMessage(),
                'stack_trace' => $e->getTraceAsString()
            ]);

            throw $e;
        }
    }

    /**
     * Fetch new posts from the RSS feed.
     *
     * @return \Illuminate\Support\Collection
     * @throws FeedProcessingException
     */
    protected function fetchNewPosts(): \Illuminate\Support\Collection
    {
        return (new rssFetcher($this))->fetch();
    }

    /**
     * Process and save new posts to the database.
     *
     * This handles saving posts, applying plugins, and batch operations
     * for better performance with large feeds.
     *
     * @param \Illuminate\Support\Collection $posts
     * @return int Number of posts successfully processed
     */
    protected function processNewPosts(\Illuminate\Support\Collection $posts): int
    {
        $processedCount = 0;

        // Process posts in chunks for better memory management
        foreach ($posts->chunk(50) as $chunk) {
            foreach ($chunk as $post) {
                $post->source_id = $this->id;
                $post->save();

                // Apply plugins (this will be improved in a later phase)
                try {
                    $post->applyPlugins();
                    $post->markMutedPhrasesAsRead();
                    $post->save();
                    $processedCount++;
                } catch (\Exception $e) {
                    // Log plugin errors but don't fail the entire operation
                    Log::warning('Plugin processing failed for post', [
                        'source_id' => $this->id,
                        'post_id' => $post->id ?? 'unsaved',
                        'error' => $e->getMessage()
                    ]);
                    $processedCount++; // Still count as processed even if plugins fail
                }
            }
        }

        return $processedCount;
    }

    /**
     * Record a successful update in the source metrics.
     */
    protected function recordSuccessfulUpdate(float $startTime, int $postsProcessed): void
    {
        $durationMs = (microtime(true) - $startTime) * 1000;

        $this->update([
            'last_fetched_at' => now(),
            'last_fetch_duration_ms' => (int) $durationMs,
            'consecutive_failures' => 0, // Reset failure counter
            'status' => self::STATUS_ACTIVE,
            'last_error_at' => null,
            'last_error_message' => null
        ]);
    }

    /**
     * Record a failed update with error details.
     */
    protected function recordFailedUpdate(float $startTime, FeedProcessingException $exception): void
    {
        $durationMs = (microtime(true) - $startTime) * 1000;
        $newFailureCount = $this->consecutive_failures + 1;

        // Determine new status based on failure count
        $newStatus = self::STATUS_WARNING;
        if ($newFailureCount >= 5) {
            $newStatus = self::STATUS_FAILED;
        }

        $this->update([
            'last_fetch_duration_ms' => (int) $durationMs,
            'consecutive_failures' => $newFailureCount,
            'last_error_at' => now(),
            'last_error_message' => $exception->getMessage(),
            'status' => $newStatus
        ]);
    }

    /**
     * Record an unexpected error.
     */
    protected function recordUnexpectedError(float $startTime, \Exception $exception): void
    {
        $durationMs = (microtime(true) - $startTime) * 1000;
        $newFailureCount = $this->consecutive_failures + 1;

        $this->update([
            'last_fetch_duration_ms' => (int) $durationMs,
            'consecutive_failures' => $newFailureCount,
            'last_error_at' => now(),
            'last_error_message' => 'Unexpected error: ' . $exception->getMessage(),
            'status' => $newFailureCount >= 5 ? self::STATUS_FAILED : self::STATUS_WARNING
        ]);
    }

    public function getLatestPosts($howmany = 60)
    {
        return Post::with(['Source', 'Category'])->where('source_id', $this->id)->OrderBy('posted_at', 'desc')->take($howmany)->get();
    }

    /**
     * These are the rules for validating field form submissions.
     *
     * @return array
     */
    public static function validationRules($create = true)
    {
        $available_fetcher_kinds = ['rss'];
        $rules = [
            'name' => 'required',
            'url' => 'required|url',
            'description' => 'max:140',
            'category_id' => 'required',
            'fetcher_source' => 'required|url',
            'fetcher_kind' => 'in:'.implode(',', $available_fetcher_kinds),
       ];

        return $rules;
    }

    public function categories()
    {
        return $this->BelongsToMany('App\Category');
    }

    public function shortname()
    {
        // www.slashdot.com --> wwwslashdotcom
        return \Illuminate\Support\Str::slug($this->url);
    }

    // FOR VERSION 2

    public function latestPostsSinceEarliestUnread()
    {
        // Get all the posts since the earliest unread one or $minimum_posts, whichever is bigger
        $minimum_posts = 10;
        $maximum_posts = 40;

        // 1- Get the date of the earliest unread post
        $earliest_unread_post = $this->posts()->where('read', 0)->orderBy('posted_at', 'asc')->take(1)->get();

        // If there are no unread posts return the latest posts
        if ($earliest_unread_post->count() < 1) {
            return $this->posts()->with(['source'])->orderBy('posted_at', 'desc')->take($minimum_posts)->get();
        }

        // Otherwise get all posts since the earliest unread post
        $date_of_earliest_uread_post = (string) $earliest_unread_post->first()->posted_at;
        $all_posts_since_earliest_unread =
        $this->posts()->with(['source'])
        ->where('posted_at', '>=', $date_of_earliest_uread_post)
        ->orderBy('posted_at', 'desc')->take($maximum_posts)->get();

        // if the posts are less than $minimum_posts get latest posts instead
        if ($all_posts_since_earliest_unread->count() < $minimum_posts) {
            return $this->posts()->with(['source'])->orderBy('posted_at', 'desc')->take($minimum_posts)->get();
        }
        // otherwise return all posts since earliest unread post
        return $all_posts_since_earliest_unread;
    }

    // ========================================
    // HEALTH AND STATUS METHODS
    // ========================================

    /**
     * Check if this source should be skipped due to exponential backoff.
     *
     * Failed sources are backed off with increasing delays to avoid
     * hammering broken feeds and wasting resources.
     */
    public function shouldSkipDueToBackoff(): bool
    {
        if ($this->consecutive_failures === 0) {
            return false;
        }

        if (!$this->last_error_at) {
            return false;
        }

        // Calculate exponential backoff: 2^failures minutes, max 24 hours (1440 minutes)
        $backoffMinutes = min(60 * (2 ** $this->consecutive_failures), 1440);

        return $this->last_error_at->addMinutes($backoffMinutes)->isFuture();
    }

    /**
     * Get human-readable status description.
     */
    public function getStatusDescription(): string
    {
        return match($this->status) {
            self::STATUS_ACTIVE => 'Working normally',
            self::STATUS_WARNING => "Issues detected ({$this->consecutive_failures} recent failures)",
            self::STATUS_FAILED => "Not working ({$this->consecutive_failures} consecutive failures)",
            default => 'Unknown status'
        };
    }

    /**
     * Check if this source is healthy (no recent failures).
     */
    public function isHealthy(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /**
     * Check if this source has warnings (some failures but still trying).
     */
    public function hasWarnings(): bool
    {
        return $this->status === self::STATUS_WARNING;
    }

    /**
     * Check if this source is considered failed.
     */
    public function isFailed(): bool
    {
        return $this->status === self::STATUS_FAILED;
    }

    /**
     * Get the next scheduled attempt time for failed sources.
     */
    public function getNextAttemptTime(): ?Carbon
    {
        if ($this->consecutive_failures === 0 || !$this->last_error_at) {
            return null;
        }

        $backoffMinutes = min(60 * (2 ** $this->consecutive_failures), 1440);
        return $this->last_error_at->addMinutes($backoffMinutes);
    }

    /**
     * Get performance metrics for this source.
     */
    public function getMetrics(): array
    {
        return [
            'last_fetched_at' => $this->last_fetched_at?->toISOString(),
            'last_fetch_duration_ms' => $this->last_fetch_duration_ms,
            'consecutive_failures' => $this->consecutive_failures,
            'status' => $this->status,
            'status_description' => $this->getStatusDescription(),
            'last_error_at' => $this->last_error_at?->toISOString(),
            'last_error_message' => $this->last_error_message,
            'next_attempt_at' => $this->getNextAttemptTime()?->toISOString(),
            'should_skip_backoff' => $this->shouldSkipDueToBackoff()
        ];
    }

    // ========================================
    // QUERY SCOPES FOR FILTERING BY STATUS
    // ========================================

    /**
     * Scope to get only healthy sources.
     */
    public function scopeHealthy($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /**
     * Scope to get sources with warnings.
     */
    public function scopeWithWarnings($query)
    {
        return $query->where('status', self::STATUS_WARNING);
    }

    /**
     * Scope to get failed sources.
     */
    public function scopeFailed($query)
    {
        return $query->where('status', self::STATUS_FAILED);
    }

    /**
     * Scope to get sources that can be processed (not in backoff).
     */
    public function scopeProcessable($query)
    {
        return $query->where('active', true)
                    ->where(function($q) {
                        $q->where('consecutive_failures', 0)
                          ->orWhere('last_error_at', null)
                          ->orWhere(function($subQ) {
                              // Calculate if backoff period has expired
                              // This is a simplified version - in practice you might use raw SQL
                              $subQ->where('consecutive_failures', '>', 0)
                                   ->where('last_error_at', '<=', now()->subHour()); // Conservative 1 hour minimum
                          });
                    });
    }
}
