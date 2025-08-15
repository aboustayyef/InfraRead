<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Source;
use App\Models\Category;
use App\ValueObjects\SourceUpdateResult;
use App\Exceptions\FeedProcessing\FeedFetchException;
use App\Exceptions\FeedProcessing\FeedParseException;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * Test suite for Source model metrics and tracking functionality.
 *
 * This tests the new performance tracking, health status, and error handling
 * features added to the Source model.
 */
class SourceMetricsTest extends TestCase
{
    use RefreshDatabase;

    protected Source $source;
    protected Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test category and source
        $this->category = Category::factory()->create(['description' => 'Test Category']);
        $this->source = Source::factory()->create([
            'name' => 'Test Source',
            'category_id' => $this->category->id,
            'fetcher_source' => 'https://example.com/feed.xml',
            'active' => true
        ]);
    }

    /** @test */
    public function source_starts_with_healthy_status()
    {
        $this->assertEquals(Source::STATUS_ACTIVE, $this->source->status);
        $this->assertEquals(0, $this->source->consecutive_failures);
        $this->assertNull($this->source->last_fetched_at);
        $this->assertNull($this->source->last_error_at);
        $this->assertTrue($this->source->isHealthy());
        $this->assertFalse($this->source->hasWarnings());
        $this->assertFalse($this->source->isFailed());
    }

    /** @test */
    public function successful_update_records_metrics_correctly()
    {
        // Simulate a successful update
        $startTime = microtime(true);
        usleep(1000); // Add 1ms delay to ensure measurable duration
        Carbon::setTestNow($testTime = now());

        // Call the protected method via reflection to test it directly
        $reflection = new \ReflectionClass($this->source);
        $method = $reflection->getMethod('recordSuccessfulUpdate');
        $method->setAccessible(true);

        $method->invoke($this->source, $startTime, 5);

        $this->source->refresh();

        // Verify metrics were recorded
        $this->assertEquals($testTime->toDateTimeString(), $this->source->last_fetched_at->toDateTimeString());
        $this->assertGreaterThan(0, $this->source->last_fetch_duration_ms);
        $this->assertEquals(0, $this->source->consecutive_failures);
        $this->assertEquals(Source::STATUS_ACTIVE, $this->source->status);
        $this->assertNull($this->source->last_error_at);
        $this->assertNull($this->source->last_error_message);
    }

    /** @test */
    public function failed_update_records_error_metrics()
    {
        $startTime = microtime(true);
        usleep(1000); // Add 1ms delay to ensure measurable duration
        Carbon::setTestNow($testTime = now());

        $exception = FeedFetchException::httpError($this->source, 404, 'Not Found');

        // Call the protected method
        $reflection = new \ReflectionClass($this->source);
        $method = $reflection->getMethod('recordFailedUpdate');
        $method->setAccessible(true);

        $method->invoke($this->source, $startTime, $exception);

        $this->source->refresh();

        // Verify error metrics
        $this->assertGreaterThan(0, $this->source->last_fetch_duration_ms);
        $this->assertEquals(1, $this->source->consecutive_failures);
        $this->assertEquals(Source::STATUS_WARNING, $this->source->status);
        $this->assertEquals($testTime->toDateTimeString(), $this->source->last_error_at->toDateTimeString());
        $this->assertStringContainsString('HTTP 404 error', $this->source->last_error_message);
    }

    /** @test */
    public function multiple_failures_change_status_to_failed()
    {
        // Simulate 5 consecutive failures
        for ($i = 1; $i <= 5; $i++) {
            $exception = FeedFetchException::timeout($this->source, 30);
            $reflection = new \ReflectionClass($this->source);
            $method = $reflection->getMethod('recordFailedUpdate');
            $method->setAccessible(true);

            $method->invoke($this->source, microtime(true), $exception);
            $this->source->refresh();
        }

        $this->assertEquals(5, $this->source->consecutive_failures);
        $this->assertEquals(Source::STATUS_FAILED, $this->source->status);
        $this->assertTrue($this->source->isFailed());
        $this->assertFalse($this->source->isHealthy());
    }

    /** @test */
    public function exponential_backoff_calculation_works_correctly()
    {
        Carbon::setTestNow($testTime = now());

        // Test with different failure counts
        $testCases = [
            1 => 120,  // 2^1 * 60 = 120 minutes
            2 => 240,  // 2^2 * 60 = 240 minutes
            3 => 480,  // 2^3 * 60 = 480 minutes
            10 => 1440, // Max 24 hours (1440 minutes)
        ];

        foreach ($testCases as $failures => $expectedMinutes) {
            $this->source->update([
                'consecutive_failures' => $failures,
                'last_error_at' => $testTime
            ]);

            // Should be skipped immediately after error
            $this->assertTrue($this->source->shouldSkipDueToBackoff());

            // Should not be skipped after backoff period
            Carbon::setTestNow($testTime->copy()->addMinutes($expectedMinutes + 1));
            $this->assertFalse($this->source->shouldSkipDueToBackoff());
        }
    }

    /** @test */
    public function next_attempt_time_calculation_is_accurate()
    {
        Carbon::setTestNow($baseTime = now());

        $this->source->update([
            'consecutive_failures' => 2,
            'last_error_at' => $baseTime
        ]);

        $expectedNextAttempt = $baseTime->copy()->addMinutes(240); // 2^2 * 60
        $actualNextAttempt = $this->source->getNextAttemptTime();

        $this->assertEquals(
            $expectedNextAttempt->toDateTimeString(),
            $actualNextAttempt->toDateTimeString()
        );
    }

    /** @test */
    public function source_metrics_method_returns_complete_data()
    {
        Carbon::setTestNow($testTime = now());

        $this->source->update([
            'last_fetched_at' => $testTime,
            'last_fetch_duration_ms' => 1500,
            'consecutive_failures' => 2,
            'status' => Source::STATUS_WARNING,
            'last_error_at' => $testTime->copy()->subHour(),
            'last_error_message' => 'Test error message'
        ]);

        $metrics = $this->source->getMetrics();

        $this->assertArrayHasKey('last_fetched_at', $metrics);
        $this->assertArrayHasKey('last_fetch_duration_ms', $metrics);
        $this->assertArrayHasKey('consecutive_failures', $metrics);
        $this->assertArrayHasKey('status', $metrics);
        $this->assertArrayHasKey('status_description', $metrics);
        $this->assertArrayHasKey('last_error_at', $metrics);
        $this->assertArrayHasKey('last_error_message', $metrics);
        $this->assertArrayHasKey('next_attempt_at', $metrics);
        $this->assertArrayHasKey('should_skip_backoff', $metrics);

        $this->assertEquals(1500, $metrics['last_fetch_duration_ms']);
        $this->assertEquals(2, $metrics['consecutive_failures']);
        $this->assertEquals(Source::STATUS_WARNING, $metrics['status']);
        $this->assertStringContainsString('Issues detected', $metrics['status_description']);
    }

    /** @test */
    public function query_scopes_filter_sources_correctly()
    {
        // Create sources with different statuses
        $healthySource = Source::factory()->create([
            'category_id' => $this->category->id,
            'status' => Source::STATUS_ACTIVE
        ]);

        $warningSource = Source::factory()->create([
            'category_id' => $this->category->id,
            'status' => Source::STATUS_WARNING
        ]);

        $failedSource = Source::factory()->create([
            'category_id' => $this->category->id,
            'status' => Source::STATUS_FAILED
        ]);

        // Test scopes
        $this->assertCount(2, Source::healthy()->get()); // Original + healthy
        $this->assertCount(1, Source::withWarnings()->get());
        $this->assertCount(1, Source::failed()->get());
    }

    /** @test */
    public function status_description_returns_meaningful_messages()
    {
        $testCases = [
            [Source::STATUS_ACTIVE, 0, 'Working normally'],
            [Source::STATUS_WARNING, 2, 'Issues detected (2 recent failures)'],
            [Source::STATUS_FAILED, 5, 'Not working (5 consecutive failures)'],
        ];

        foreach ($testCases as [$status, $failures, $expectedMessage]) {
            $this->source->update([
                'status' => $status,
                'consecutive_failures' => $failures
            ]);

            $this->assertEquals($expectedMessage, $this->source->getStatusDescription());
        }
    }
}
