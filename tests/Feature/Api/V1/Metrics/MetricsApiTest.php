<?php

namespace Tests\Feature\Api\V1\Metrics;

use App\Models\Source;
use App\Models\Post;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;
use Carbon\Carbon;
use PHPUnit\Framework\Attributes\Test;

/**
 * Test metrics API endpoints for monitoring and observability
 *
 * These tests verify that our metrics endpoints provide accurate data
 * about system health, source performance, and processing statistics.
 *
 * Teaching Note: Metrics endpoints are crucial for:
 * - System monitoring and alerting
 * - Performance analysis and optimization
 * - Debugging issues with specific sources
 * - Capacity planning and scaling decisions
 */
class MetricsApiTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->category = Category::factory()->create();
    }

    #[Test]
    public function source_metrics_endpoint_returns_comprehensive_data()
    {
        $source = Source::factory()->create([
            'category_id' => $this->category->id,
            'name' => 'Test Source',
            'fetcher_source' => 'https://example.com/feed.xml',
            'last_fetched_at' => now()->subHour(),
            'last_fetch_duration_ms' => 1500,
            'consecutive_failures' => 0,
            'status' => Source::STATUS_ACTIVE
        ]);

        // Create some posts for this source
        Post::factory()->count(5)->create(['source_id' => $source->id, 'read' => true]);
        Post::factory()->count(2)->create(['source_id' => $source->id, 'read' => false]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/v1/metrics/sources/{$source->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'source_id',
                    'source_name',
                    'source_url',
                    'metrics' => [
                        'last_fetched_at',
                        'last_fetch_duration_ms',
                        'consecutive_failures',
                        'last_error_at',
                        'last_error_message',
                        'status',
                        'status_description',
                        'next_attempt_at',
                        'should_skip_backoff',
                        'posts_count',
                        'unread_posts_count',
                        'latest_post_date',
                        'is_healthy',
                        'is_failed'
                    ]
                ]
            ])
            ->assertJson([
                'data' => [
                    'source_id' => $source->id,
                    'source_name' => 'Test Source',
                    'source_url' => 'https://example.com/feed.xml',
                    'metrics' => [
                        'status' => Source::STATUS_ACTIVE,
                        'consecutive_failures' => 0,
                        'posts_count' => 7, // 5 read + 2 unread
                        'unread_posts_count' => 2,
                        'should_skip_backoff' => false,
                        'is_healthy' => true,
                        'is_failed' => false
                    ]
                ]
            ]);
    }

    #[Test]
    public function source_metrics_shows_backoff_info_for_failing_source()
    {
        $source = Source::factory()->create([
            'category_id' => $this->category->id,
            'consecutive_failures' => 3,
            'last_error_at' => now()->subMinutes(30),
            'last_error_message' => 'HTTP 500 error',
            'status' => Source::STATUS_WARNING
        ]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/v1/metrics/sources/{$source->id}");

        $response->assertStatus(200);

        // Check if the response includes backoff information
        $data = $response->json('data.metrics');

        $this->assertEquals(3, $data['consecutive_failures']);
        $this->assertEquals('HTTP 500 error', $data['last_error_message']);
        $this->assertEquals(Source::STATUS_WARNING, $data['status']);

        // If source is in backoff, next_attempt_at should be present
        if ($data['should_skip_backoff']) {
            $this->assertArrayHasKey('next_attempt_at', $data);
        }
    }

    #[Test]
    public function system_stats_endpoint_returns_comprehensive_overview()
    {
        // Create test data
        $healthySource = Source::factory()->create([
            'category_id' => $this->category->id,
            'active' => true,
            'status' => Source::STATUS_ACTIVE
        ]);

        $warningSource = Source::factory()->create([
            'category_id' => $this->category->id,
            'active' => true,
            'status' => Source::STATUS_WARNING,
            'consecutive_failures' => 2
        ]);

        $inactiveSource = Source::factory()->create([
            'category_id' => $this->category->id,
            'active' => false,
            'status' => Source::STATUS_WARNING  // Give it a non-healthy status
        ]);

        // Create posts
        Post::factory()->count(10)->create([
            'source_id' => $healthySource->id,
            'read' => true,
            'created_at' => now()->subDays(2) // Older posts
        ]);
        Post::factory()->count(5)->create([
            'source_id' => $healthySource->id,
            'read' => false,
            'created_at' => now()->subDay() // Yesterday posts
        ]);
        Post::factory()->count(3)->create([
            'source_id' => $healthySource->id,
            'created_at' => today(), // Today's posts
            'read' => true
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/metrics/system');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'sources' => [
                        'total_sources',
                        'active_sources',
                        'healthy_sources',
                        'warning_sources',
                        'failed_sources'
                    ],
                    'posts' => [
                        'total_posts',
                        'unread_posts',
                        'posts_today',
                        'posts_this_week',
                        'posts_this_month'
                    ],
                    'categories' => [
                        'total_categories',
                        'categories_with_sources'
                    ],
                    'performance' => [
                        'sources_updated_today',
                        'average_fetch_duration_ms',
                        'fastest_source_ms',
                        'slowest_source_ms'
                    ],
                    'errors' => [
                        'sources_with_errors',
                        'total_consecutive_failures',
                        'sources_in_backoff'
                    ],
                    'generated_at',
                    'cache_duration'
                ]
            ]);

        $data = $response->json('data');

        // Verify some of the calculated values
        $this->assertEquals(3, $data['sources']['total_sources']);
        $this->assertEquals(2, $data['sources']['active_sources']);
        $this->assertEquals(1, $data['sources']['healthy_sources']);
        $this->assertEquals(2, $data['sources']['warning_sources']); // Now 2: warningSource + inactiveSource
        $this->assertEquals(18, $data['posts']['total_posts']); // 10 + 5 + 3
        $this->assertEquals(5, $data['posts']['unread_posts']);
        $this->assertEquals(3, $data['posts']['posts_today']);
    }

    #[Test]
    public function system_stats_are_cached()
    {
        // Clear any existing cache
        Cache::forget('system_processing_stats');

        Source::factory()->create(['category_id' => $this->category->id]);

        // First request should generate the cache
        $response1 = $this->actingAs($this->user)
            ->getJson('/api/v1/metrics/system');

        $response1->assertStatus(200);

        // Verify cache exists
        $this->assertTrue(Cache::has('system_processing_stats'));

        // Second request should use cached data
        $response2 = $this->actingAs($this->user)
            ->getJson('/api/v1/metrics/system');

        $response2->assertStatus(200);

        // Both responses should be identical (using cached data)
        $this->assertEquals($response1->json(), $response2->json());
    }

    #[Test]
    public function sources_health_endpoint_shows_problematic_sources()
    {
        // Create sources with different health statuses
        $healthySource = Source::factory()->create([
            'category_id' => $this->category->id,
            'name' => 'Healthy Source',
            'active' => true,
            'status' => Source::STATUS_ACTIVE,
            'consecutive_failures' => 0
        ]);

        $warningSource = Source::factory()->create([
            'category_id' => $this->category->id,
            'name' => 'Warning Source',
            'active' => true,
            'status' => Source::STATUS_WARNING,
            'consecutive_failures' => 2,
            'last_error_at' => now()->subHour(),
            'last_error_message' => 'Connection timeout'
        ]);

        $failedSource = Source::factory()->create([
            'category_id' => $this->category->id,
            'name' => 'Failed Source',
            'active' => true,
            'status' => Source::STATUS_FAILED,
            'consecutive_failures' => 5,
            'last_error_at' => now()->subMinutes(30),
            'last_error_message' => 'Invalid RSS feed'
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/metrics/sources-health');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'summary' => [
                        'total',
                        'active',
                        'inactive',
                        'healthy',
                        'warning',
                        'failed'
                    ],
                    'problematic_sources' => [
                        '*' => [
                            'id',
                            'name',
                            'status',
                            'consecutive_failures',
                            'last_error_at',
                            'last_error_message',
                            'status_description'
                        ]
                    ],
                    'generated_at'
                ]
            ]);

        $data = $response->json('data');

        // Verify summary counts
        $this->assertEquals(3, $data['summary']['total']);
        $this->assertEquals(3, $data['summary']['active']);
        $this->assertEquals(1, $data['summary']['healthy']);
        $this->assertEquals(1, $data['summary']['warning']);
        $this->assertEquals(1, $data['summary']['failed']);

        // Verify problematic sources are listed (warning and failed)
        $this->assertCount(2, $data['problematic_sources']);

        $sourceNames = collect($data['problematic_sources'])->pluck('name');
        $this->assertTrue($sourceNames->contains('Warning Source'));
        $this->assertTrue($sourceNames->contains('Failed Source'));
        $this->assertFalse($sourceNames->contains('Healthy Source'));
    }

    #[Test]
    public function recent_activity_endpoint_shows_recent_updates()
    {
        // Create sources with different last_fetched_at times
        $recentSource = Source::factory()->create([
            'category_id' => $this->category->id,
            'name' => 'Recent Source',
            'last_fetched_at' => now()->subHours(2),
            'last_fetch_duration_ms' => 1200,
            'status' => Source::STATUS_ACTIVE
        ]);

        $oldSource = Source::factory()->create([
            'category_id' => $this->category->id,
            'name' => 'Old Source',
            'last_fetched_at' => now()->subDays(2), // More than 24 hours ago
            'last_fetch_duration_ms' => 800
        ]);

        // Create some recent posts
        Post::factory()->count(5)->create([
            'source_id' => $recentSource->id,
            'created_at' => now()->subHours(3)
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/metrics/recent-activity');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'recent_posts_count',
                    'recently_updated_sources' => [
                        '*' => [
                            'source_id',
                            'source_name',
                            'last_fetched_at',
                            'duration_ms',
                            'status',
                            'consecutive_failures'
                        ]
                    ],
                    'time_range',
                    'generated_at'
                ]
            ]);

        $data = $response->json('data');

        // Should show recent posts
        $this->assertEquals(5, $data['recent_posts_count']);

        // Should only show recently updated source (within 24 hours)
        $this->assertCount(1, $data['recently_updated_sources']);
        $this->assertEquals('Recent Source', $data['recently_updated_sources'][0]['source_name']);
    }

    #[Test]
    public function metrics_endpoints_require_authentication()
    {
        $source = Source::factory()->create(['category_id' => $this->category->id]);

        $endpoints = [
            "/api/v1/metrics/sources/{$source->id}",
            '/api/v1/metrics/system',
            '/api/v1/metrics/sources-health',
            '/api/v1/metrics/recent-activity'
        ];

        foreach ($endpoints as $endpoint) {
            $response = $this->getJson($endpoint);
            $response->assertStatus(401);
        }
    }

    #[Test]
    public function source_metrics_handles_nonexistent_source()
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/metrics/sources/99999');

        $response->assertStatus(404);
    }
}
