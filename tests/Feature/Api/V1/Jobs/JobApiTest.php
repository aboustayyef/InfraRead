<?php

namespace Tests\Feature\Api\V1\Jobs;

use App\Models\Source;
use App\Models\Post;
use App\Models\Category;
use App\Models\User;
use App\Jobs\RefreshSourceJob;
use App\Jobs\GenerateSummaryJob;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

/**
 * Test job-based API endpoints for background processing
 *
 * These tests verify that our new job-based endpoints correctly dispatch
 * background jobs instead of executing operations immediately. This is
 * important for ensuring good API performance and reliability.
 *
 * Teaching Note: When testing background jobs, we typically:
 * 1. Fake the queue system to prevent actual job execution
 * 2. Assert that the correct job was dispatched with correct parameters
 * 3. Test the job itself separately to ensure it works properly
 * 4. Verify the API responses are appropriate for async operations (202 status)
 */
class JobApiTest extends TestCase
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

    /** @test */
    public function refresh_source_job_endpoint_dispatches_job()
    {
        Queue::fake(); // Prevent actual job execution

        $source = Source::factory()->create([
            'category_id' => $this->category->id
        ]);

        $response = $this->actingAs($this->user)
            ->postJson("/api/v1/jobs/sources/{$source->id}/refresh");

        $response->assertStatus(202) // HTTP 202 = Accepted for processing
            ->assertJsonStructure([
                'message',
                'data' => [
                    'source_id',
                    'source_name',
                    'job_id',
                    'status'
                ]
            ])
            ->assertJson([
                'data' => [
                    'source_id' => $source->id,
                    'source_name' => $source->name,
                    'status' => 'queued'
                ]
            ]);

        // Verify the job was dispatched
        Queue::assertPushed(RefreshSourceJob::class, function ($job) use ($source) {
            return $job->source->id === $source->id;
        });
    }

    /** @test */
    public function refresh_source_job_requires_authentication()
    {
        $source = Source::factory()->create([
            'category_id' => $this->category->id
        ]);

        $response = $this->postJson("/api/v1/jobs/sources/{$source->id}/refresh");

        $response->assertStatus(401);
    }

    /** @test */
    public function refresh_source_job_handles_nonexistent_source()
    {
        $response = $this->actingAs($this->user)
            ->postJson("/api/v1/jobs/sources/99999/refresh");

        $response->assertStatus(404);
    }

    /** @test */
    public function generate_summary_job_endpoint_dispatches_job()
    {
        Queue::fake();

        $post = Post::factory()->create([
            'source_id' => Source::factory()->create(['category_id' => $this->category->id])->id
        ]);

        $response = $this->actingAs($this->user)
            ->postJson("/api/v1/jobs/posts/{$post->id}/summary", [
                'sentences' => 3
            ]);

        $response->assertStatus(202)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'post_id',
                    'post_title',
                    'sentences',
                    'cache_key',
                    'status',
                    'check_status_url'
                ]
            ])
            ->assertJson([
                'data' => [
                    'post_id' => $post->id,
                    'post_title' => $post->title,
                    'sentences' => 3,
                    'status' => 'queued'
                ]
            ]);

        // Verify the job was dispatched with correct parameters
        Queue::assertPushed(GenerateSummaryJob::class, function ($job) use ($post) {
            return $job->post->id === $post->id && $job->sentences === 3;
        });
    }

    /** @test */
    public function generate_summary_job_uses_default_sentences()
    {
        Queue::fake();

        $post = Post::factory()->create([
            'source_id' => Source::factory()->create(['category_id' => $this->category->id])->id
        ]);

        $response = $this->actingAs($this->user)
            ->postJson("/api/v1/jobs/posts/{$post->id}/summary");

        $response->assertStatus(202)
            ->assertJson([
                'data' => [
                    'sentences' => 2 // Default value
                ]
            ]);

        Queue::assertPushed(GenerateSummaryJob::class, function ($job) use ($post) {
            return $job->post->id === $post->id && $job->sentences === 2;
        });
    }

    /** @test */
    public function generate_summary_job_validates_sentences_parameter()
    {
        $post = Post::factory()->create([
            'source_id' => Source::factory()->create(['category_id' => $this->category->id])->id
        ]);

        // Test invalid sentences values
        $invalidValues = [0, -1, 11, 'abc', ''];

        foreach ($invalidValues as $value) {
            $response = $this->actingAs($this->user)
                ->postJson("/api/v1/jobs/posts/{$post->id}/summary", [
                    'sentences' => $value
                ]);

            $response->assertStatus(422);
        }
    }

    /** @test */
    public function summary_status_endpoint_handles_nonexistent_key()
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/jobs/summary-status/nonexistent-key');

        $response->assertStatus(404)
            ->assertJson([
                'status' => 'not_found',
                'message' => 'Summary job not found or expired'
            ]);
    }

    /** @test */
    public function summary_status_endpoint_returns_processing_status()
    {
        $cacheKey = 'test_summary_key';

        // Simulate a job in processing state
        Cache::put($cacheKey . '_status', 'processing', 300);

        $response = $this->actingAs($this->user)
            ->getJson("/api/v1/jobs/summary-status/{$cacheKey}");

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'processing',
                'message' => 'Summary is being generated'
            ]);
    }

    /** @test */
    public function summary_status_endpoint_returns_completed_result()
    {
        $cacheKey = 'test_summary_key';

        // Simulate a completed job result
        $completedResult = [
            'status' => 'completed',
            'data' => [
                'post_id' => 123,
                'sentences' => 2,
                'summary' => 'This is a test summary.',
                'generated_at' => now()->toISOString()
            ]
        ];

        Cache::put($cacheKey, $completedResult, 1800);

        $response = $this->actingAs($this->user)
            ->getJson("/api/v1/jobs/summary-status/{$cacheKey}");

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'completed',
                'data' => $completedResult['data']
            ]);
    }

    /** @test */
    public function summary_status_endpoint_returns_error_result()
    {
        $cacheKey = 'test_summary_key';

        // Simulate a failed job result
        $errorResult = [
            'status' => 'failed',
            'error' => 'OpenAI API error',
            'post_id' => 123,
            'failed_at' => now()->toISOString()
        ];

        Cache::put($cacheKey, $errorResult, 300);

        $response = $this->actingAs($this->user)
            ->getJson("/api/v1/jobs/summary-status/{$cacheKey}");

        $response->assertStatus(422)
            ->assertJson([
                'status' => 'failed',
                'error' => 'OpenAI API error',
                'message' => 'Summary generation failed'
            ]);
    }

    /** @test */
    public function queue_status_endpoint_returns_system_info()
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/jobs/queue-status');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'refresh_queue',
                    'summaries_queue',
                    'system_time',
                    'note'
                ]
            ]);
    }

    /** @test */
    public function job_endpoints_require_authentication()
    {
        $source = Source::factory()->create(['category_id' => $this->category->id]);
        $post = Post::factory()->create(['source_id' => $source->id]);

        $endpoints = [
            ['POST', "/api/v1/jobs/sources/{$source->id}/refresh"],
            ['POST', "/api/v1/jobs/posts/{$post->id}/summary"],
            ['GET', '/api/v1/jobs/summary-status/test-key'],
            ['GET', '/api/v1/jobs/queue-status']
        ];

        foreach ($endpoints as [$method, $url]) {
            $response = $this->json($method, $url);
            $response->assertStatus(401);
        }
    }
}
