<?php

namespace App\Console\Commands;

use App\Models\Source;
use App\ValueObjects\SourceUpdateResult;
use App\Exceptions\FeedProcessing\FeedProcessingException;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class PostsUpdater extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update_posts {source?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update Database with New posts';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * This command processes RSS feeds for all active sources or a specific source.
     * It includes comprehensive error handling, performance tracking, and respects
     * exponential backoff for failed sources.
     *
     * @return int Exit code (0 = success, 1 = failure)
     */
    public function handle(): int
    {
        $overallStartTime = microtime(true);

        try {
            // Handle single source processing
            if ($this->argument('source')) {
                return $this->processSingleSource($this->argument('source'));
            }

            // Handle all sources processing
            return $this->processAllSources($overallStartTime);

        } catch (\Exception $e) {
            $this->error('Critical error in feed processing: ' . $e->getMessage());
            Log::critical('PostsUpdater command failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return 1; // Exit with error code
        }
    }

    /**
     * Process a single source by ID.
     */
    protected function processSingleSource(string $sourceId): int
    {
        $source = Source::find($sourceId);

        if (!$source) {
            $this->error("Source [{$sourceId}] not found");
            return 1;
        }

        $this->info("Processing source: {$source->name}");

        try {
            $result = $source->updatePosts();

            if ($result->isSuccess()) {
                $this->info($result->getSummary());
                $this->info('✓ Done');
                return 0;
            } else {
                $this->error($result->getSummary());
                return 1;
            }

        } catch (FeedProcessingException $e) {
            $this->error("Feed processing failed: {$e->getMessage()}");
            return 1;
        }
    }

    /**
     * Process all active sources with intelligent filtering and error handling.
     */
    protected function processAllSources(float $overallStartTime): int
    {
        // Get sources that are active and not in exponential backoff
        $sources = Source::where('active', true)->get();

        $this->info("Found {$sources->count()} active sources");

        $stats = [
            'total' => 0,
            'processed' => 0,
            'skipped' => 0,
            'succeeded' => 0,
            'failed' => 0,
            'posts_processed' => 0
        ];

        foreach ($sources as $source) {
            $stats['total']++;

            // Check if source should be skipped due to exponential backoff
            if ($source->shouldSkipDueToBackoff()) {
                $nextAttempt = $source->getNextAttemptTime();
                $this->warn("⏳ Skipping {$source->name} (backoff until {$nextAttempt->format('Y-m-d H:i:s')})");
                $stats['skipped']++;
                continue;
            }

            $this->comment("🔄 Processing: {$source->name}");
            $stats['processed']++;

            try {
                $result = $source->updatePosts();

                if ($result->isSuccess()) {
                    $this->info("✓ {$result->getSummary()}");
                    $stats['succeeded']++;
                    $stats['posts_processed'] += $result->postsProcessed;
                } else {
                    $this->error("✗ {$result->getSummary()}");
                    $stats['failed']++;
                }

            } catch (FeedProcessingException $e) {
                $this->error("✗ Failed: {$e->getMessage()}");
                $stats['failed']++;

                // Continue processing other sources even if one fails
                continue;

            } catch (\Exception $e) {
                $this->error("✗ Unexpected error: {$e->getMessage()}");
                $stats['failed']++;

                // Log unexpected errors for debugging
                Log::error('Unexpected error processing source', [
                    'source_id' => $source->id,
                    'source_name' => $source->name,
                    'error' => $e->getMessage()
                ]);

                continue;
            }
        }

        // Calculate and display summary
        $totalDuration = microtime(true) - $overallStartTime;
        $this->displayProcessingSummary($stats, $totalDuration);

        // Update last successful crawl timestamp using cache
        Cache::forever('last_successful_crawl', now());

        // Log summary for monitoring
        Log::info('Feed processing completed', [
            'stats' => $stats,
            'duration_seconds' => $totalDuration
        ]);

        // Return appropriate exit code
        return $stats['failed'] > 0 ? 1 : 0;
    }

    /**
     * Display a comprehensive processing summary.
     */
    protected function displayProcessingSummary(array $stats, float $duration): void
    {
        $this->newLine();
        $this->info('📊 PROCESSING SUMMARY');
        $this->info('═══════════════════');
        $this->info("Total sources: {$stats['total']}");
        $this->info("Processed: {$stats['processed']}");
        $this->info("Skipped (backoff): {$stats['skipped']}");
        $this->info("Succeeded: {$stats['succeeded']}");
        $this->info("Failed: {$stats['failed']}");
        $this->info("Posts processed: {$stats['posts_processed']}");
        $this->info("Duration: " . number_format($duration, 2) . " seconds");

        // Show status if there were failures
        if ($stats['failed'] > 0) {
            $this->warn("⚠️  {$stats['failed']} sources had errors - check logs for details");
        }

        if ($stats['skipped'] > 0) {
            $this->comment("ℹ️  {$stats['skipped']} sources skipped due to exponential backoff");
        }
    }
}
