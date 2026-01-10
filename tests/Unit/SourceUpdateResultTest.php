<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\ValueObjects\SourceUpdateResult;
use PHPUnit\Framework\Attributes\Test;

/**
 * Test suite for SourceUpdateResult value object.
 *
 * This ensures our result object works correctly and provides
 * consistent interfaces for success and failure cases.
 */
class SourceUpdateResultTest extends TestCase
{
    #[Test]
    public function can_create_successful_result()
    {
        $result = SourceUpdateResult::success(
            postsProcessed: 5,
            durationSeconds: 2.5,
            context: ['source' => 'test']
        );

        $this->assertTrue($result->isSuccess());
        $this->assertFalse($result->isFailure());
        $this->assertEquals(5, $result->postsProcessed);
        $this->assertEquals(2.5, $result->durationSeconds);
        $this->assertNull($result->errorMessage);
        $this->assertNull($result->errorType);
        $this->assertEquals(['source' => 'test'], $result->context);
    }

    #[Test]
    public function can_create_failed_result()
    {
        $result = SourceUpdateResult::failure(
            durationSeconds: 1.8,
            errorMessage: 'Network timeout',
            errorType: 'fetch_failure',
            context: ['timeout_seconds' => 30]
        );

        $this->assertFalse($result->isSuccess());
        $this->assertTrue($result->isFailure());
        $this->assertEquals(0, $result->postsProcessed); // Should be 0 for failures
        $this->assertEquals(1.8, $result->durationSeconds);
        $this->assertEquals('Network timeout', $result->errorMessage);
        $this->assertEquals('fetch_failure', $result->errorType);
        $this->assertEquals(['timeout_seconds' => 30], $result->context);
    }

    #[Test]
    public function success_summary_format_is_correct()
    {
        $result = SourceUpdateResult::success(
            postsProcessed: 10,
            durationSeconds: 3.14159
        );

        $summary = $result->getSummary();

        $this->assertEquals(
            'Successfully processed 10 posts in 3.14 seconds',
            $summary
        );
    }

    #[Test]
    public function failure_summary_format_is_correct()
    {
        $result = SourceUpdateResult::failure(
            durationSeconds: 2.71828,
            errorMessage: 'Feed not found',
            errorType: 'fetch_failure'
        );

        $summary = $result->getSummary();

        $this->assertEquals(
            'Failed after 2.72 seconds: Feed not found',
            $summary
        );
    }

    #[Test]
    public function to_array_returns_complete_data_for_success()
    {
        $result = SourceUpdateResult::success(
            postsProcessed: 7,
            durationSeconds: 1.5,
            context: ['test' => 'data']
        );

        $array = $result->toArray();

        $expected = [
            'success' => true,
            'posts_processed' => 7,
            'duration_seconds' => 1.5,
            'error_message' => null,
            'error_type' => null,
            'context' => ['test' => 'data']
        ];

        $this->assertEquals($expected, $array);
    }

    #[Test]
    public function to_array_returns_complete_data_for_failure()
    {
        $result = SourceUpdateResult::failure(
            durationSeconds: 0.8,
            errorMessage: 'Invalid XML',
            errorType: 'parse_failure',
            context: ['line' => 42]
        );

        $array = $result->toArray();

        $expected = [
            'success' => false,
            'posts_processed' => 0,
            'duration_seconds' => 0.8,
            'error_message' => 'Invalid XML',
            'error_type' => 'parse_failure',
            'context' => ['line' => 42]
        ];

        $this->assertEquals($expected, $array);
    }

    #[Test]
    public function readonly_properties_cannot_be_modified()
    {
        $result = SourceUpdateResult::success(5, 2.0);

        // This should not be possible with readonly properties
        // We're testing that PHP 8.1+ readonly behavior works as expected
        $this->expectException(\Error::class);
        $result->success = false; // Should throw error
    }

    #[Test]
    public function constructor_sets_all_properties_correctly()
    {
        $result = new SourceUpdateResult(
            success: true,
            postsProcessed: 3,
            durationSeconds: 4.5,
            errorMessage: 'test error',
            errorType: 'test_type',
            context: ['key' => 'value']
        );

        $this->assertTrue($result->success);
        $this->assertEquals(3, $result->postsProcessed);
        $this->assertEquals(4.5, $result->durationSeconds);
        $this->assertEquals('test error', $result->errorMessage);
        $this->assertEquals('test_type', $result->errorType);
        $this->assertEquals(['key' => 'value'], $result->context);
    }

    #[Test]
    public function default_values_work_correctly()
    {
        $result = new SourceUpdateResult(
            success: false,
            postsProcessed: 0,
            durationSeconds: 1.0
        );

        // Test default values
        $this->assertNull($result->errorMessage);
        $this->assertNull($result->errorType);
        $this->assertEquals([], $result->context);
    }
}
