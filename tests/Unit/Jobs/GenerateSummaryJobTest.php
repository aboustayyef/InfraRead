<?php

namespace Tests\Unit\Jobs;

use App\Jobs\GenerateSummaryJob;
use App\Models\Post;
use App\Models\Source;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Client\RequestException;
use Tests\TestCase;
use Mockery;

/**
 * Unit tests for the GenerateSummaryJob
 *
 * These tests verify the AI summary generation job handles:
 * - Successful summary generation and caching
 * - API errors and retry logic
 * - Different types of failures (retryable vs permanent)
 * - Proper cache management for status tracking
 *
 * Teaching Note: AI integration jobs are particularly important to test because:
 * - External APIs are unreliable and can fail in many ways
 * - Rate limiting and costs make retries expensive
 * - Users expect status updates for long-running operations
 * - Caching logic is complex and error-prone
 */
class GenerateSummaryJobTest extends TestCase
{
    use RefreshDatabase;

    protected Category $category;
    protected Source $source;

    protected function setUp(): void
    {
        parent::setUp();
        $this->category = Category::factory()->create();
        $this->source = Source::factory()->create(['category_id' => $this->category->id]);
    }

    /** @test */
    public function job_successfully_generates_and_caches_summary()
    {
        $post = Post::factory()->create(['source_id' => $this->source->id]);
        $cacheKey = 'test_summary_key';

        // Mock the post's summary method to return a successful result
        $expectedSummary = 'This is a test summary. It has two sentences.';
        $mockedPost = Mockery::mock($post)->makePartial();
        $mockedPost->shouldReceive('summary')
                  ->with(2)
                  ->once()
                  ->andReturn($expectedSummary);

        $job = new GenerateSummaryJob($mockedPost, 2, $cacheKey);

        // Mock job properties
        $job->job = Mockery::mock();
        $job->job->shouldReceive('getJobId')->andReturn('test-job-123');

        $job = Mockery::mock($job)->makePartial();
        $job->shouldReceive('attempts')->andReturn(1);

        Log::shouldReceive('info')->twice(); // Start and completion logs

        // Clear any existing cache
        Cache::forget($cacheKey);
        Cache::forget($cacheKey . '_status');

        $job->handle();

        // Verify the result was cached
        $cachedResult = Cache::get($cacheKey);
        $this->assertNotNull($cachedResult);
        $this->assertEquals('completed', $cachedResult['status']);
        $this->assertEquals($expectedSummary, $cachedResult['data']['summary']);
        $this->assertEquals(2, $cachedResult['data']['sentences']);

        // Verify processing status was removed
        $this->assertFalse(Cache::has($cacheKey . '_status'));
    }

    /** @test */
    public function job_handles_ai_api_error()
    {
        $post = Post::factory()->create(['source_id' => $this->source->id]);
        $cacheKey = 'test_summary_key';

        // Mock the post's summary method to return an error
        $mockedPost = Mockery::mock($post)->makePartial();
        $mockedPost->shouldReceive('summary')
                  ->with(2)
                  ->once()
                  ->andReturn('Error summarizing');

        $job = new GenerateSummaryJob($mockedPost, 2, $cacheKey);

        $job->job = Mockery::mock();
        $job->job->shouldReceive('getJobId')->andReturn('test-job-123');

        $job = Mockery::mock($job)->makePartial();
        $job->shouldReceive('attempts')->andReturn(1);

        Log::shouldReceive('info')->once(); // Start log
        Log::shouldReceive('error')->once(); // Error log
        Log::shouldReceive('info')->once(); // Will retry log

        Cache::forget($cacheKey);
        Cache::forget($cacheKey . '_status');

        // Should throw exception for retry
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('AI summary generation failed: Error summarizing');

        $job->handle();

        // Verify error was cached
        $cachedResult = Cache::get($cacheKey);
        $this->assertNotNull($cachedResult);
        $this->assertEquals('error', $cachedResult['status']);
        $this->assertStringContains('AI summary generation failed', $cachedResult['error']);
    }

    /** @test */
    public function job_handles_http_request_exception()
    {
        $post = Post::factory()->create(['source_id' => $this->source->id]);
        $cacheKey = 'test_summary_key';

        // Mock the post's summary method to throw a RequestException
        $requestException = new RequestException('Network timeout');

        $mockedPost = Mockery::mock($post)->makePartial();
        $mockedPost->shouldReceive('summary')
                  ->with(2)
                  ->once()
                  ->andThrow($requestException);

        $job = new GenerateSummaryJob($mockedPost, 2, $cacheKey);

        $job->job = Mockery::mock();
        $job->job->shouldReceive('getJobId')->andReturn('test-job-123');

        $job = Mockery::mock($job)->makePartial();
        $job->shouldReceive('attempts')->andReturn(1);

        Log::shouldReceive('info')->twice(); // Start + will retry logs
        Log::shouldReceive('error')->once(); // Error log

        Cache::forget($cacheKey);
        Cache::forget($cacheKey . '_status');

        // Should re-throw for retry
        $this->expectException(RequestException::class);

        $job->handle();

        // Verify error was cached
        $cachedResult = Cache::get($cacheKey);
        $this->assertNotNull($cachedResult);
        $this->assertEquals('error', $cachedResult['status']);
        $this->assertStringContains('HTTP error', $cachedResult['error']);
    }

    /** @test */
    public function job_fails_permanently_after_max_attempts()
    {
        $post = Post::factory()->create(['source_id' => $this->source->id]);
        $cacheKey = 'test_summary_key';

        $mockedPost = Mockery::mock($post)->makePartial();
        $mockedPost->shouldReceive('summary')
                  ->with(2)
                  ->once()
                  ->andReturn('Error summarizing');

        $job = new GenerateSummaryJob($mockedPost, 2, $cacheKey);

        $job->job = Mockery::mock();
        $job->job->shouldReceive('getJobId')->andReturn('test-job-123');

        // Simulate max attempts reached
        $job = Mockery::mock($job)->makePartial();
        $job->shouldReceive('attempts')->andReturn(3); // Max attempts
        $job->shouldReceive('fail')->once();

        Log::shouldReceive('info')->once(); // Start log
        Log::shouldReceive('error')->twice(); // Error + permanent failure logs

        Cache::forget($cacheKey);
        Cache::forget($cacheKey . '_status');

        $job->handle();

        // Verify error was cached
        $cachedResult = Cache::get($cacheKey);
        $this->assertNotNull($cachedResult);
        $this->assertEquals('error', $cachedResult['status']);
    }

    /** @test */
    public function job_handles_non_retryable_errors_correctly()
    {
        $post = Post::factory()->create(['source_id' => $this->source->id]);
        $cacheKey = 'test_summary_key';

        // Simulate a non-retryable error (e.g., invalid API key)
        $mockedPost = Mockery::mock($post)->makePartial();
        $mockedPost->shouldReceive('summary')
                  ->with(2)
                  ->once()
                  ->andReturn('Error: Invalid API key');

        $job = new GenerateSummaryJob($mockedPost, 2, $cacheKey);

        $job->job = Mockery::mock();
        $job->job->shouldReceive('getJobId')->andReturn('test-job-123');

        $job = Mockery::mock($job)->makePartial();
        $job->shouldReceive('attempts')->andReturn(1);
        $job->shouldReceive('fail')->once(); // Should fail immediately

        Log::shouldReceive('info')->once(); // Start log
        Log::shouldReceive('error')->twice(); // Error + permanent failure logs

        Cache::forget($cacheKey);
        Cache::forget($cacheKey . '_status');

        $job->handle();
    }

    /** @test */
    public function job_sets_processing_status_in_cache()
    {
        $post = Post::factory()->create(['source_id' => $this->source->id]);
        $cacheKey = 'test_summary_key';

        $mockedPost = Mockery::mock($post)->makePartial();
        $mockedPost->shouldReceive('summary')
                  ->with(2)
                  ->once()
                  ->andReturn('Test summary.');

        $job = new GenerateSummaryJob($mockedPost, 2, $cacheKey);

        $job->job = Mockery::mock();
        $job->job->shouldReceive('getJobId')->andReturn('test-job-123');

        $job = Mockery::mock($job)->makePartial();
        $job->shouldReceive('attempts')->andReturn(1);

        Log::shouldReceive('info')->twice();

        Cache::forget($cacheKey);
        Cache::forget($cacheKey . '_status');

        $job->handle();

        // Processing status should be removed after completion
        $this->assertFalse(Cache::has($cacheKey . '_status'));

        // But the result should be cached
        $this->assertTrue(Cache::has($cacheKey));
    }

    /** @test */
    public function job_failed_method_caches_permanent_failure()
    {
        $post = Post::factory()->create(['source_id' => $this->source->id]);
        $cacheKey = 'test_summary_key';

        $job = new GenerateSummaryJob($post, 2, $cacheKey);

        $job->job = Mockery::mock();
        $job->job->shouldReceive('getJobId')->andReturn('test-job-123');

        $job = Mockery::mock($job)->makePartial();
        $job->shouldReceive('attempts')->andReturn(3);

        $exception = new \Exception('Permanent failure');

        Log::shouldReceive('error')->once();

        Cache::forget($cacheKey);
        Cache::forget($cacheKey . '_status');

        $job->failed($exception);

        // Verify permanent failure was cached
        $cachedResult = Cache::get($cacheKey);
        $this->assertNotNull($cachedResult);
        $this->assertEquals('failed', $cachedResult['status']);
        $this->assertStringContains('Permanent failure', $cachedResult['error']);
    }

    /** @test */
    public function job_uses_correct_configuration()
    {
        $post = Post::factory()->create(['source_id' => $this->source->id]);
        $job = new GenerateSummaryJob($post, 3, 'test_key');

        // Verify job configuration
        $this->assertEquals(3, $job->tries);
        $this->assertEquals(60, $job->timeout);
        $this->assertEquals('summaries', $job->queue);

        // Verify backoff settings
        $expectedBackoff = [10, 30, 120];
        $this->assertEquals($expectedBackoff, $job->backoff());

        // Verify stored properties
        $this->assertEquals($post->id, $job->post->id);
        $this->assertEquals(3, $job->sentences);
        $this->assertEquals('test_key', $job->cacheKey);
    }

    /** @test */
    public function job_generates_cache_key_if_none_provided()
    {
        $post = Post::factory()->create(['source_id' => $this->source->id]);
        $job = new GenerateSummaryJob($post, 2);

        // Should generate a cache key automatically
        $this->assertNotNull($job->cacheKey);
        $this->assertStringStartsWith("summary_job_post_{$post->id}_", $job->cacheKey);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
