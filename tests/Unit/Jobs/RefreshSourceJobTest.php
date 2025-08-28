<?php

namespace Tests\Unit\Jobs;

use App\Jobs\RefreshSourceJob;
use App\Models\Source;
use App\Models\Category;
use App\ValueObjects\SourceUpdateResult;
use App\Exceptions\FeedProcessing\FeedFetchException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;
use Mockery;

/**
 * Unit tests for the RefreshSourceJob
 *
 * These tests verify that the job correctly handles different scenarios:
 * - Successful RSS feed refresh
 * - Various types of errors and retries
 * - Proper logging and error handling
 *
 * Teaching Note: Unit testing jobs is important because:
 * - Jobs often handle complex error scenarios
 * - Retry logic needs to be tested thoroughly
 * - Logging and monitoring depend on proper job behavior
 * - Jobs are often hard to test manually in production
 */
class RefreshSourceJobTest extends TestCase
{
    use RefreshDatabase;

    protected Category $category;

    protected function setUp(): void
    {
        parent::setUp();
        $this->category = Category::factory()->create();
    }

    /** @test */
    public function job_successfully_refreshes_source()
    {
        // Create a source and mock its updatePosts method
        $source = Source::factory()->create(['category_id' => $this->category->id]);

        $successResult = SourceUpdateResult::success(
            postsProcessed: 5,
            durationSeconds: 2.5
        );

        // Mock the source to return a successful result
        $mockedSource = Mockery::mock($source)->makePartial();
        $mockedSource->shouldReceive('updatePosts')
                    ->once()
                    ->andReturn($successResult);

        // Create and handle the job
        $job = new RefreshSourceJob($mockedSource);

        // Mock the job's internal properties that would be set by Laravel
        $job->job = Mockery::mock();
        $job->job->shouldReceive('getJobId')->andReturn('test-job-123');

        Log::shouldReceive('info')->twice(); // Start and completion logs

        $job->handle();

        // If we get here without exceptions, the job succeeded
        $this->assertTrue(true);
    }

    /** @test */
    public function job_handles_retryable_feed_processing_exception()
    {
        $source = Source::factory()->create(['category_id' => $this->category->id]);

        // Create a retryable exception
        $exception = FeedFetchException::timeout($source, 30);

        $mockedSource = Mockery::mock($source)->makePartial();
        $mockedSource->shouldReceive('updatePosts')
                    ->once()
                    ->andThrow($exception);

        $job = new RefreshSourceJob($mockedSource);

        // Mock job properties
        $job->job = Mockery::mock();
        $job->job->shouldReceive('getJobId')->andReturn('test-job-123');

        // Mock attempts() to simulate this is attempt #1 of 3
        $job = Mockery::mock($job)->makePartial();
        $job->shouldReceive('attempts')->andReturn(1);

        Log::shouldReceive('info')->once(); // Start log
        Log::shouldReceive('error')->once(); // Error log
        Log::shouldReceive('info')->once(); // Will retry log

        // Should re-throw for retry since it's retryable and under max attempts
        $this->expectException(FeedFetchException::class);

        $job->handle();
    }

    /** @test */
    public function job_fails_permanently_for_non_retryable_exception()
    {
        $source = Source::factory()->create(['category_id' => $this->category->id]);

        // Create a non-retryable exception
        $exception = FeedFetchException::invalidFeed($source, 'Not a valid RSS feed');

        $mockedSource = Mockery::mock($source)->makePartial();
        $mockedSource->shouldReceive('updatePosts')
                    ->once()
                    ->andThrow($exception);

        $job = new RefreshSourceJob($mockedSource);

        // Mock job properties
        $job->job = Mockery::mock();
        $job->job->shouldReceive('getJobId')->andReturn('test-job-123');

        $job = Mockery::mock($job)->makePartial();
        $job->shouldReceive('attempts')->andReturn(1);
        $job->shouldReceive('fail')->once(); // Should call fail() for non-retryable

        Log::shouldReceive('info')->once(); // Start log
        Log::shouldReceive('error')->twice(); // Error log + permanent failure log

        $job->handle();
    }

    /** @test */
    public function job_fails_permanently_after_max_attempts()
    {
        $source = Source::factory()->create(['category_id' => $this->category->id]);

        $exception = FeedFetchException::timeout($source, 30);

        $mockedSource = Mockery::mock($source)->makePartial();
        $mockedSource->shouldReceive('updatePosts')
                    ->once()
                    ->andThrow($exception);

        $job = new RefreshSourceJob($mockedSource);

        $job->job = Mockery::mock();
        $job->job->shouldReceive('getJobId')->andReturn('test-job-123');

        // Simulate max attempts reached
        $job = Mockery::mock($job)->makePartial();
        $job->shouldReceive('attempts')->andReturn(3); // At max attempts
        $job->shouldReceive('fail')->once();

        Log::shouldReceive('info')->once(); // Start log
        Log::shouldReceive('error')->twice(); // Error log + permanent failure log

        $job->handle();
    }

    /** @test */
    public function job_handles_unexpected_exceptions()
    {
        $source = Source::factory()->create(['category_id' => $this->category->id]);

        // Simulate an unexpected exception
        $exception = new \Exception('Unexpected database error');

        $mockedSource = Mockery::mock($source)->makePartial();
        $mockedSource->shouldReceive('updatePosts')
                    ->once()
                    ->andThrow($exception);

        $job = new RefreshSourceJob($mockedSource);

        $job->job = Mockery::mock();
        $job->job->shouldReceive('getJobId')->andReturn('test-job-123');

        $job = Mockery::mock($job)->makePartial();
        $job->shouldReceive('attempts')->andReturn(1);

        Log::shouldReceive('info')->once(); // Start log
        Log::shouldReceive('error')->once(); // Error log

        // Should re-throw for retry on unexpected errors
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Unexpected database error');

        $job->handle();
    }

    /** @test */
    public function job_failed_method_logs_properly()
    {
        $source = Source::factory()->create(['category_id' => $this->category->id]);
        $job = new RefreshSourceJob($source);

        // Mock job properties
        $job->job = Mockery::mock();
        $job->job->shouldReceive('getJobId')->andReturn('test-job-123');

        $job = Mockery::mock($job)->makePartial();
        $job->shouldReceive('attempts')->andReturn(3);

        $exception = new \Exception('Final failure');

        Log::shouldReceive('error')->once()->with(
            'Manual source refresh job failed permanently',
            Mockery::type('array')
        );

        $job->failed($exception);
    }

    /** @test */
    public function job_uses_correct_queue_name()
    {
        $source = Source::factory()->create(['category_id' => $this->category->id]);
        $job = new RefreshSourceJob($source);

        // The job should be configured for the 'refresh' queue
        $this->assertEquals('refresh', $job->queue);
    }

    /** @test */
    public function job_has_correct_retry_configuration()
    {
        $source = Source::factory()->create(['category_id' => $this->category->id]);
        $job = new RefreshSourceJob($source);

        // Verify retry settings
        $this->assertEquals(3, $job->tries);
        $this->assertEquals(120, $job->timeout);

        // Verify backoff settings
        $expectedBackoff = [30, 120, 480];
        $this->assertEquals($expectedBackoff, $job->backoff());
    }

    /** @test */
    public function job_stores_source_correctly()
    {
        $source = Source::factory()->create(['category_id' => $this->category->id]);
        $job = new RefreshSourceJob($source);

        // The job should store the source for serialization
        $this->assertEquals($source->id, $job->source->id);
        $this->assertEquals($source->name, $job->source->name);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
