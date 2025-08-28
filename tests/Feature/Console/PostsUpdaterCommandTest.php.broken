<?php

namespace Tests\Feature\Console;

use Tests\TestCase;
use App\Models\Source;
use App\Models\User;
use App\Models\Category;
use App\Exceptions\FeedProcessing\FeedFetchException;
use App\Exceptions\FeedProcessing\FeedParseException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class PostsUpdaterCommandTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test user and category
        $this->user = User::factory()->create();
        $this->category = Category::factory()->create();

        // Mock the file storage
        Storage::fake('local');

        // Clear any logs
        Log::spy();
    }

    /** @test */
    public function can_process_single_source_successfully()
    {
        // Create a source
        $source = Source::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
            'url' => 'https://example.com/feed.xml',
            'name' => 'Test Feed'
        ]);

        // Mock RSS response
        Http::fake([
            'example.com/feed.xml' => Http::response(
                '<?xml version="1.0"?>
                <rss version="2.0">
                    <channel>
                        <title>Test Feed</title>
                        <item>
                            <title>Test Post</title>
                            <link>https://example.com/post1</link>
                            <description>Test content</description>
                            <pubDate>Wed, 01 Jan 2020 12:00:00 GMT</pubDate>
                        </item>
                    </channel>
                </rss>',
                200
            )
        ]);

        // Run the command
        $this->artisan('posts:update', ['--source' => $source->id])
             ->expectsOutput('Processing source: Test Feed')
             ->assertExitCode(0);

        // Verify post was created
        $this->assertDatabaseHas('posts', [
            'title' => 'Test Post',
            'url' => 'https://example.com/post1',
            'source_id' => $source->id
        ]);
    }

    /** @test */
    public function processes_source_with_valid_feed()
    {
        $source = Source::factory()->create([
            'category_id' => $this->category->id,
            'url' => 'https://example.com/feed.xml',
            'active' => true
        ]);

        // Mock successful RSS feed response
        Http::fake([
            'example.com/feed.xml' => Http::response($this->getValidRssFeed(), 200, [
                'Content-Type' => 'application/rss+xml'
            ])
        ]);

        // Run the command for single source
        $this->artisan('app:update_posts', ['source' => $source->id])
             ->expectsOutput("Processing source: {$source->name}")
             ->expectsOutputToContain('✓ Done')
             ->assertExitCode(0);

        // Verify source was updated
        $source->refresh();
        $this->assertEquals('healthy', $source->status);
        $this->assertNotNull($source->last_fetched_at);
        $this->assertEquals(0, $source->consecutive_failures);
    }

    /** @test */
    public function handles_single_source_not_found()
    {
        // Run command with non-existent source ID
        $this->artisan('app:update_posts', ['source' => 999])
             ->expectsOutput('Source [999] not found')
             ->assertExitCode(1);
    }

    /** @test */
    public function handles_single_source_feed_fetch_error()
    {
        // Create a test source
        $source = Source::factory()->create([
            'category_id' => $this->category->id,
            'url' => 'https://example.com/feed.xml',
            'active' => true
        ]);

        // Mock failed HTTP response
        Http::fake([
            'example.com/feed.xml' => Http::response('Not Found', 404)
        ]);

        // Run the command
        $this->artisan('app:update_posts', ['source' => $source->id])
             ->expectsOutput("Processing source: {$source->name}")
             ->expectsOutputToContain('Feed processing failed')
             ->assertExitCode(1);

        // Verify error was recorded
        $source->refresh();
        $this->assertEquals('failed', $source->status);
        $this->assertEquals(1, $source->consecutive_failures);
    }

    /** @test */
    public function processes_all_active_sources()
    {
        // Create multiple test sources
        $activeSource1 = Source::factory()->create([
            'category_id' => $this->category->id,
            'url' => 'https://example1.com/feed.xml',
            'active' => true,
            'name' => 'Test Source 1'
        ]);

        $activeSource2 = Source::factory()->create([
            'category_id' => $this->category->id,
            'url' => 'https://example2.com/feed.xml',
            'active' => true,
            'name' => 'Test Source 2'
        ]);

        $inactiveSource = Source::factory()->create([
            'category_id' => $this->category->id,
            'url' => 'https://example3.com/feed.xml',
            'active' => false,
            'name' => 'Inactive Source'
        ]);

        // Mock successful responses for active sources
        Http::fake([
            'example1.com/feed.xml' => Http::response($this->getValidRssFeed(), 200),
            'example2.com/feed.xml' => Http::response($this->getValidRssFeed(), 200),
        ]);

        // Run the command without source argument
        $this->artisan('app:update_posts')
             ->expectsOutput('Found 2 active sources')
             ->expectsOutputToContain('🔄 Processing: Test Source 1')
             ->expectsOutputToContain('🔄 Processing: Test Source 2')
             ->expectsOutputToContain('📊 PROCESSING SUMMARY')
             ->expectsOutputToContain('Total sources: 2')
             ->expectsOutputToContain('Succeeded: 2')
             ->expectsOutputToContain('Failed: 0')
             ->assertExitCode(0);

        // Verify all active sources were updated
        $activeSource1->refresh();
        $activeSource2->refresh();
        $inactiveSource->refresh();

        $this->assertEquals('healthy', $activeSource1->status);
        $this->assertEquals('healthy', $activeSource2->status);
        $this->assertNull($inactiveSource->last_fetched_at); // Should not be processed
    }

    /** @test */
    public function skips_sources_in_exponential_backoff()
    {
        // Create a source with failures that should trigger backoff
        $source = Source::factory()->create([
            'category_id' => $this->category->id,
            'url' => 'https://example.com/feed.xml',
            'active' => true,
            'consecutive_failures' => 3,
            'last_error_at' => now()->subMinutes(5), // 5 minutes ago
            'status' => 'failed'
        ]);

        // Run the command
        $this->artisan('app:update_posts')
             ->expectsOutput('Found 1 active sources')
             ->expectsOutputToContain("⏳ Skipping {$source->name} (backoff until")
             ->expectsOutputToContain('Skipped (backoff): 1')
             ->expectsOutputToContain('Processed: 0')
             ->assertExitCode(0);

        // Verify source wasn't processed
        $originalLastFetched = $source->last_fetched_at;
        $source->refresh();
        $this->assertEquals($originalLastFetched, $source->last_fetched_at);
    }

    /** @test */
    public function handles_mixed_success_and_failure_scenarios()
    {
        // Create sources with different outcomes
        $successSource = Source::factory()->create([
            'category_id' => $this->category->id,
            'url' => 'https://success.com/feed.xml',
            'active' => true,
            'name' => 'Success Source'
        ]);

        $failSource = Source::factory()->create([
            'category_id' => $this->category->id,
            'url' => 'https://fail.com/feed.xml',
            'active' => true,
            'name' => 'Fail Source'
        ]);

        // Mock responses
        Http::fake([
            'success.com/feed.xml' => Http::response($this->getValidRssFeed(), 200),
            'fail.com/feed.xml' => Http::response('Server Error', 500)
        ]);

        // Run the command
        $this->artisan('app:update_posts')
             ->expectsOutput('Found 2 active sources')
             ->expectsOutputToContain('✓') // Success indicator
             ->expectsOutputToContain('✗') // Failure indicator
             ->expectsOutputToContain('Succeeded: 1')
             ->expectsOutputToContain('Failed: 1')
             ->expectsOutputToContain('⚠️  1 sources had errors - check logs for details')
             ->assertExitCode(1); // Should exit with error code due to failures

        // Verify individual source states
        $successSource->refresh();
        $failSource->refresh();

        $this->assertEquals('healthy', $successSource->status);
        $this->assertEquals('failed', $failSource->status);
    }

    /** @test */
    public function logs_processing_summary()
    {
        // Create a test source
        $source = Source::factory()->create([
            'category_id' => $this->category->id,
            'url' => 'https://example.com/feed.xml',
            'active' => true
        ]);

        // Mock successful response
        Http::fake([
            'example.com/feed.xml' => Http::response($this->getValidRssFeed(), 200)
        ]);

        // Run the command
        $this->artisan('app:update_posts')
             ->assertExitCode(0);

        // Verify logging occurred
        Log::shouldHaveReceived('info')
           ->with('Feed processing completed', \Mockery::type('array'))
           ->once();
    }

    /** @test */
    public function updates_last_successful_crawl_timestamp()
    {
        // Create a test source
        $source = Source::factory()->create([
            'category_id' => $this->category->id,
            'url' => 'https://example.com/feed.xml',
            'active' => true
        ]);

        // Mock successful response
        Http::fake([
            'example.com/feed.xml' => Http::response($this->getValidRssFeed(), 200)
        ]);

        // Run the command
        $this->artisan('app:update_posts')
             ->assertExitCode(0);

        // Verify timestamp file was created
        Storage::disk('local')->assertExists('LastSuccessfulCrawl.txt');

        $timestamp = Storage::disk('local')->get('LastSuccessfulCrawl.txt');
        $this->assertNotEmpty($timestamp);

        // Verify it's a valid ISO timestamp
        $parsed = Carbon::parse($timestamp);
        $this->assertInstanceOf(Carbon::class, $parsed);
    }

    /** @test */
    public function handles_critical_errors_gracefully()
    {
        // Create a source that will cause issues
        $source = Source::factory()->create([
            'category_id' => $this->category->id,
            'url' => 'invalid-url',
            'active' => true
        ]);

        // Run the command - should handle the error gracefully
        $this->artisan('app:update_posts')
             ->expectsOutputToContain('✗') // Error indicator
             ->assertExitCode(1);

        // Verify error logging occurred
        Log::shouldHaveReceived('error')
           ->with('Unexpected error processing source', \Mockery::type('array'))
           ->once();
    }

    /**
     * Helper method to generate valid RSS feed XML for testing.
     */
    protected function getValidRssFeed(): string
    {
        return '<?xml version="1.0" encoding="UTF-8"?>
        <rss version="2.0">
            <channel>
                <title>Test Feed</title>
                <description>A test RSS feed</description>
                <link>https://example.com</link>
                <item>
                    <title>Test Article</title>
                    <description>This is a test article</description>
                    <link>https://example.com/article1</link>
                    <guid>article1</guid>
                    <pubDate>' . now()->toRSSString() . '</pubDate>
                </item>
                <item>
                    <title>Another Test Article</title>
                    <description>This is another test article</description>
                    <link>https://example.com/article2</link>
                    <guid>article2</guid>
                    <pubDate>' . now()->subDay()->toRSSString() . '</pubDate>
                </item>
            </channel>
        </rss>';
    }
}

        // Mock successful RSS feed response
        Http::fake([
            'example.com/feed.xml' => Http::response($this->getValidRssFeed(), 200, [
                'Content-Type' => 'application/rss+xml'
            ])
        ]);

        // Run the command for single source
        $this->artisan('app:update_posts', ['source' => $source->id])
             ->expectsOutput("Processing source: {$source->name}")
             ->expectsOutputToContain('✓ Done')
             ->assertExitCode(0);

        // Verify source was updated
        $source->refresh();
        $this->assertEquals('healthy', $source->status);
        $this->assertNotNull($source->last_fetched_at);
        $this->assertEquals(0, $source->consecutive_failures);
    }

    /** @test */
    public function handles_single_source_not_found()
    {
        // Run command with non-existent source ID
        $this->artisan('app:update_posts', ['source' => 999])
             ->expectsOutput('Source [999] not found')
             ->assertExitCode(1);
    }

    /** @test */
    public function handles_single_source_feed_fetch_error()
    {
        // Create a test source
        $source = Source::factory()->create([
            '
            'category_id' => $this->category->id,
            'url' => 'https://example.com/feed.xml',
            'active' => true
        ]);

        // Mock failed HTTP response
        Http::fake([
            'example.com/feed.xml' => Http::response('Not Found', 404)
        ]);

        // Run the command
        $this->artisan('app:update_posts', ['source' => $source->id])
             ->expectsOutput("Processing source: {$source->name}")
             ->expectsOutputToContain('Feed processing failed')
             ->assertExitCode(1);

        // Verify error was recorded
        $source->refresh();
        $this->assertEquals('failed', $source->status);
        $this->assertEquals(1, $source->consecutive_failures);
    }

    /** @test */
    public function processes_all_active_sources()
    {
        // Create multiple test sources
        $activeSource1 = Source::factory()->create([
            '
            'category_id' => $this->category->id,
            'url' => 'https://example1.com/feed.xml',
            'active' => true,
            'name' => 'Test Source 1'
        ]);

        $activeSource2 = Source::factory()->create([
            '
            'category_id' => $this->category->id,
            'url' => 'https://example2.com/feed.xml',
            'active' => true,
            'name' => 'Test Source 2'
        ]);

        $inactiveSource = Source::factory()->create([
            '
            'category_id' => $this->category->id,
            'url' => 'https://example3.com/feed.xml',
            'active' => false,
            'name' => 'Inactive Source'
        ]);

        // Mock successful responses for active sources
        Http::fake([
            'example1.com/feed.xml' => Http::response($this->getValidRssFeed(), 200),
            'example2.com/feed.xml' => Http::response($this->getValidRssFeed(), 200),
        ]);

        // Run the command without source argument
        $this->artisan('app:update_posts')
             ->expectsOutput('Found 2 active sources')
             ->expectsOutputToContain('🔄 Processing: Test Source 1')
             ->expectsOutputToContain('🔄 Processing: Test Source 2')
             ->expectsOutputToContain('📊 PROCESSING SUMMARY')
             ->expectsOutputToContain('Total sources: 2')
             ->expectsOutputToContain('Succeeded: 2')
             ->expectsOutputToContain('Failed: 0')
             ->assertExitCode(0);

        // Verify all active sources were updated
        $activeSource1->refresh();
        $activeSource2->refresh();
        $inactiveSource->refresh();

        $this->assertEquals('healthy', $activeSource1->status);
        $this->assertEquals('healthy', $activeSource2->status);
        $this->assertNull($inactiveSource->last_fetched_at); // Should not be processed
    }

    /** @test */
    public function skips_sources_in_exponential_backoff()
    {
        // Create a source with failures that should trigger backoff
        $source = Source::factory()->create([
            '
            'category_id' => $this->category->id,
            'url' => 'https://example.com/feed.xml',
            'active' => true,
            'consecutive_failures' => 3,
            'last_error_at' => now()->subMinutes(5), // 5 minutes ago
            'status' => 'failed'
        ]);

        // Run the command
        $this->artisan('app:update_posts')
             ->expectsOutput('Found 1 active sources')
             ->expectsOutputToContain("⏳ Skipping {$source->name} (backoff until")
             ->expectsOutputToContain('Skipped (backoff): 1')
             ->expectsOutputToContain('Processed: 0')
             ->assertExitCode(0);

        // Verify source wasn't processed
        $originalLastFetched = $source->last_fetched_at;
        $source->refresh();
        $this->assertEquals($originalLastFetched, $source->last_fetched_at);
    }

    /** @test */
    public function handles_mixed_success_and_failure_scenarios()
    {
        // Create sources with different outcomes
        $successSource = Source::factory()->create([
            '
            'category_id' => $this->category->id,
            'url' => 'https://success.com/feed.xml',
            'active' => true,
            'name' => 'Success Source'
        ]);

        $failSource = Source::factory()->create([
            '
            'category_id' => $this->category->id,
            'url' => 'https://fail.com/feed.xml',
            'active' => true,
            'name' => 'Fail Source'
        ]);

        // Mock responses
        Http::fake([
            'success.com/feed.xml' => Http::response($this->getValidRssFeed(), 200),
            'fail.com/feed.xml' => Http::response('Server Error', 500)
        ]);

        // Run the command
        $this->artisan('app:update_posts')
             ->expectsOutput('Found 2 active sources')
             ->expectsOutputToContain('✓') // Success indicator
             ->expectsOutputToContain('✗') // Failure indicator
             ->expectsOutputToContain('Succeeded: 1')
             ->expectsOutputToContain('Failed: 1')
             ->expectsOutputToContain('⚠️  1 sources had errors - check logs for details')
             ->assertExitCode(1); // Should exit with error code due to failures

        // Verify individual source states
        $successSource->refresh();
        $failSource->refresh();

        $this->assertEquals('healthy', $successSource->status);
        $this->assertEquals('failed', $failSource->status);
    }

    /** @test */
    public function logs_processing_summary()
    {
        // Create a test source
        $source = Source::factory()->create([
            '
            'category_id' => $this->category->id,
            'url' => 'https://example.com/feed.xml',
            'active' => true
        ]);

        // Mock successful response
        Http::fake([
            'example.com/feed.xml' => Http::response($this->getValidRssFeed(), 200)
        ]);

        // Run the command
        $this->artisan('app:update_posts')
             ->assertExitCode(0);

        // Verify logging occurred
        Log::shouldHaveReceived('info')
           ->with('Feed processing completed', \Mockery::type('array'))
           ->once();
    }

    /** @test */
    public function updates_last_successful_crawl_timestamp()
    {
        // Create a test source
        $source = Source::factory()->create([
            '
            'category_id' => $this->category->id,
            'url' => 'https://example.com/feed.xml',
            'active' => true
        ]);

        // Mock successful response
        Http::fake([
            'example.com/feed.xml' => Http::response($this->getValidRssFeed(), 200)
        ]);

        // Run the command
        $this->artisan('app:update_posts')
             ->assertExitCode(0);

        // Verify timestamp file was created
        Storage::disk('local')->assertExists('LastSuccessfulCrawl.txt');

        $timestamp = Storage::disk('local')->get('LastSuccessfulCrawl.txt');
        $this->assertNotEmpty($timestamp);

        // Verify it's a valid ISO timestamp
        $parsed = Carbon::parse($timestamp);
        $this->assertInstanceOf(Carbon::class, $parsed);
    }

    /** @test */
    public function handles_critical_errors_gracefully()
    {
        // Create a source that will cause issues
        $source = Source::factory()->create([
            '
            'category_id' => $this->category->id,
            'url' => 'invalid-url',
            'active' => true
        ]);

        // Run the command - should handle the error gracefully
        $this->artisan('app:update_posts')
             ->expectsOutputToContain('✗') // Error indicator
             ->assertExitCode(1);

        // Verify error logging occurred
        Log::shouldHaveReceived('error')
           ->with('Unexpected error processing source', \Mockery::type('array'))
           ->once();
    }

    /**
     * Helper method to generate valid RSS feed XML for testing.
     */
    protected function getValidRssFeed(): string
    {
        return '<?xml version="1.0" encoding="UTF-8"?>
        <rss version="2.0">
            <channel>
                <title>Test Feed</title>
                <description>A test RSS feed</description>
                <link>https://example.com</link>
                <item>
                    <title>Test Article</title>
                    <description>This is a test article</description>
                    <link>https://example.com/article1</link>
                    <guid>article1</guid>
                    <pubDate>' . now()->toRSSString() . '</pubDate>
                </item>
                <item>
                    <title>Another Test Article</title>
                    <description>This is another test article</description>
                    <link>https://example.com/article2</link>
                    <guid>article2</guid>
                    <pubDate>' . now()->subDay()->toRSSString() . '</pubDate>
                </item>
            </channel>
        </rss>';
    }
}
