<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Source;
use App\Models\Category;
use App\Exceptions\FeedProcessing\FeedFetchException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

/**
 * Feature tests for the improved PostsUpdater console command.
 *
 * This tests the overall flow of the command, including error handling,
 * backoff logic, and comprehensive reporting.
 */
class ImprovedPostsUpdaterTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Mock storage for testing
        Storage::fake('local');

        // Suppress logs during testing (can be removed if you want to see logs)
        Log::shouldReceive('info')->byDefault();
        Log::shouldReceive('error')->byDefault();
        Log::shouldReceive('warning')->byDefault();
        Log::shouldReceive('critical')->byDefault();
    }

    /** @test */
    public function command_handles_non_existent_source_gracefully()
    {
        $this->artisan('app:update_posts', ['source' => 999])
             ->expectsOutput('Source [999] not found')
             ->assertExitCode(1);
    }

    /** @test */
    public function command_processes_single_source_successfully()
    {
        $category = Category::factory()->create();
        $source = Source::factory()->create([
            'name' => 'Test Source',
            'category_id' => $category->id,
            'active' => true
        ]);

        // Mock the updatePosts method to return success
        $source = \Mockery::mock($source)->makePartial();
        $source->shouldReceive('updatePosts')
               ->once()
               ->andReturn(\App\ValueObjects\SourceUpdateResult::success(5, 2.5));

        Source::shouldReceive('find')
              ->with((string) $source->id)
              ->andReturn($source);

        $this->artisan('app:update_posts', ['source' => $source->id])
             ->expectsOutput('Processing source: Test Source')
             ->expectsOutput('Successfully processed 5 posts in 2.50 seconds')
             ->expectsOutput('✓ Done')
             ->assertExitCode(0);
    }

    /** @test */
    public function command_handles_single_source_failure()
    {
        $category = Category::factory()->create();
        $source = Source::factory()->create([
            'name' => 'Failed Source',
            'category_id' => $category->id,
            'active' => true
        ]);

        // Mock the updatePosts method to throw an exception
        $source = \Mockery::mock($source)->makePartial();
        $exception = FeedFetchException::httpError($source, 404);
        $source->shouldReceive('updatePosts')
               ->once()
               ->andThrow($exception);

        Source::shouldReceive('find')
              ->with((string) $source->id)
              ->andReturn($source);

        $this->artisan('app:update_posts', ['source' => $source->id])
             ->expectsOutput('Processing source: Failed Source')
             ->expectsOutputToContain('Feed processing failed')
             ->assertExitCode(1);
    }

    /** @test */
    public function command_skips_sources_in_backoff()
    {
        $category = Category::factory()->create();

        // Create sources with different statuses
        $activeSource = Source::factory()->create([
            'name' => 'Active Source',
            'category_id' => $category->id,
            'active' => true,
            'consecutive_failures' => 0
        ]);

        $backoffSource = Source::factory()->create([
            'name' => 'Backoff Source',
            'category_id' => $category->id,
            'active' => true,
            'consecutive_failures' => 2,
            'last_error_at' => now()->subMinutes(60) // Not enough time has passed
        ]);

        // Mock successful processing for active source
        Source::shouldReceive('where->get')
              ->andReturn(collect([$activeSource, $backoffSource]));

        $activeSource = \Mockery::mock($activeSource)->makePartial();
        $activeSource->shouldReceive('shouldSkipDueToBackoff')->andReturn(false);
        $activeSource->shouldReceive('updatePosts')
                    ->andReturn(\App\ValueObjects\SourceUpdateResult::success(3, 1.5));

        $backoffSource = \Mockery::mock($backoffSource)->makePartial();
        $backoffSource->shouldReceive('shouldSkipDueToBackoff')->andReturn(true);
        $backoffSource->shouldReceive('getNextAttemptTime')
                     ->andReturn(now()->addMinutes(180));

        $this->artisan('app:update_posts')
             ->expectsOutput('Found 2 active sources')
             ->expectsOutputToContain('⏳ Skipping Backoff Source')
             ->expectsOutputToContain('🔄 Processing: Active Source')
             ->expectsOutputToContain('✓ Successfully processed 3 posts')
             ->expectsOutputToContain('📊 PROCESSING SUMMARY')
             ->expectsOutputToContain('Total sources: 2')
             ->expectsOutputToContain('Processed: 1')
             ->expectsOutputToContain('Skipped (backoff): 1')
             ->expectsOutputToContain('Succeeded: 1')
             ->expectsOutputToContain('Failed: 0')
             ->assertExitCode(0);
    }

    /** @test */
    public function command_continues_processing_after_individual_failures()
    {
        $category = Category::factory()->create();

        $source1 = Source::factory()->create([
            'name' => 'Source 1',
            'category_id' => $category->id,
            'active' => true
        ]);

        $source2 = Source::factory()->create([
            'name' => 'Source 2',
            'category_id' => $category->id,
            'active' => true
        ]);

        Source::shouldReceive('where->get')
              ->andReturn(collect([$source1, $source2]));

        // First source fails
        $source1 = \Mockery::mock($source1)->makePartial();
        $source1->shouldReceive('shouldSkipDueToBackoff')->andReturn(false);
        $source1->shouldReceive('updatePosts')
               ->andThrow(FeedFetchException::timeout($source1, 30));

        // Second source succeeds
        $source2 = \Mockery::mock($source2)->makePartial();
        $source2->shouldReceive('shouldSkipDueToBackoff')->andReturn(false);
        $source2->shouldReceive('updatePosts')
               ->andReturn(\App\ValueObjects\SourceUpdateResult::success(2, 1.0));

        $this->artisan('app:update_posts')
             ->expectsOutputToContain('🔄 Processing: Source 1')
             ->expectsOutputToContain('✗ Failed')
             ->expectsOutputToContain('🔄 Processing: Source 2')
             ->expectsOutputToContain('✓ Successfully processed 2 posts')
             ->expectsOutputToContain('Succeeded: 1')
             ->expectsOutputToContain('Failed: 1')
             ->expectsOutputToContain('⚠️  1 sources had errors')
             ->assertExitCode(1); // Exit code 1 because there were failures
    }

    /** @test */
    public function command_updates_last_successful_crawl_timestamp()
    {
        $category = Category::factory()->create();
        $source = Source::factory()->create([
            'category_id' => $category->id,
            'active' => true
        ]);

        Source::shouldReceive('where->get')
              ->andReturn(collect([$source]));

        $source = \Mockery::mock($source)->makePartial();
        $source->shouldReceive('shouldSkipDueToBackoff')->andReturn(false);
        $source->shouldReceive('updatePosts')
               ->andReturn(\App\ValueObjects\SourceUpdateResult::success(1, 0.5));

        Carbon::setTestNow($testTime = now());

        $this->artisan('app:update_posts')
             ->assertExitCode(0);

        // Check that the timestamp was written
        Storage::disk('local')->assertExists('LastSuccessfulCrawl.txt');
        $timestamp = Storage::disk('local')->get('LastSuccessfulCrawl.txt');
        $this->assertEquals($testTime->toISOString(), $timestamp);
    }

    /** @test */
    public function command_displays_comprehensive_summary()
    {
        $category = Category::factory()->create();

        // Create multiple sources for comprehensive testing
        $sources = collect([
            Source::factory()->create(['name' => 'Active 1', 'category_id' => $category->id, 'active' => true]),
            Source::factory()->create(['name' => 'Active 2', 'category_id' => $category->id, 'active' => true]),
            Source::factory()->create(['name' => 'Failed', 'category_id' => $category->id, 'active' => true]),
            Source::factory()->create([
                'name' => 'Backoff',
                'category_id' => $category->id,
                'active' => true,
                'consecutive_failures' => 3,
                'last_error_at' => now()->subMinutes(60)
            ])
        ]);

        Source::shouldReceive('where->get')->andReturn($sources);

        // Mock each source's behavior
        foreach ($sources as $index => $source) {
            $mockedSource = \Mockery::mock($source)->makePartial();

            if ($source->name === 'Backoff') {
                $mockedSource->shouldReceive('shouldSkipDueToBackoff')->andReturn(true);
                $mockedSource->shouldReceive('getNextAttemptTime')->andReturn(now()->addHours(3));
            } elseif ($source->name === 'Failed') {
                $mockedSource->shouldReceive('shouldSkipDueToBackoff')->andReturn(false);
                $mockedSource->shouldReceive('updatePosts')
                           ->andThrow(FeedFetchException::httpError($source, 500));
            } else {
                $mockedSource->shouldReceive('shouldSkipDueToBackoff')->andReturn(false);
                $mockedSource->shouldReceive('updatePosts')
                           ->andReturn(\App\ValueObjects\SourceUpdateResult::success(2, 1.0));
            }

            $sources[$index] = $mockedSource;
        }

        $this->artisan('app:update_posts')
             ->expectsOutputToContain('📊 PROCESSING SUMMARY')
             ->expectsOutputToContain('═══════════════════')
             ->expectsOutputToContain('Total sources: 4')
             ->expectsOutputToContain('Processed: 3')
             ->expectsOutputToContain('Skipped (backoff): 1')
             ->expectsOutputToContain('Succeeded: 2')
             ->expectsOutputToContain('Failed: 1')
             ->expectsOutputToContain('Posts processed: 4') // 2 + 2 from successful sources
             ->expectsOutputToContain('Duration:')
             ->expectsOutputToContain('⚠️  1 sources had errors')
             ->expectsOutputToContain('ℹ️  1 sources skipped due to exponential backoff')
             ->assertExitCode(1);
    }

    protected function tearDown(): void
    {
        \Mockery::close();
        parent::tearDown();
    }
}
