<?php

namespace Tests\Feature\Api\V1;

use App\Models\Category;
use App\Models\Post;
use App\Models\Source;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MarkAllReadApiTest extends TestCase
{
    use RefreshDatabase;

    protected function actingUser(): User
    {
        return User::factory()->create();
    }

    protected function createTestData(): array
    {
        // Create categories and sources
        $category1 = Category::factory()->create(['description' => 'Tech']);
        $category2 = Category::factory()->create(['description' => 'News']);

        $source1 = Source::factory()->create(['category_id' => $category1->id, 'name' => 'TechBlog']);
        $source2 = Source::factory()->create(['category_id' => $category2->id, 'name' => 'NewsSource']);

        // Create posts with different dates and read status
        $oldDate = Carbon::now()->subDays(7);
        $newDate = Carbon::now()->subDays(1);

        return [
            'category1' => $category1,
            'category2' => $category2,
            'source1' => $source1,
            'source2' => $source2,
            'posts' => [
                // Tech category, old posts
                'tech_old_unread' => Post::factory()->create([
                    'source_id' => $source1->id,
                    'category_id' => $category1->id,
                    'read' => false,
                    'posted_at' => $oldDate
                ]),
                'tech_old_read' => Post::factory()->create([
                    'source_id' => $source1->id,
                    'category_id' => $category1->id,
                    'read' => true,
                    'posted_at' => $oldDate
                ]),

                // Tech category, new posts
                'tech_new_unread' => Post::factory()->create([
                    'source_id' => $source1->id,
                    'category_id' => $category1->id,
                    'read' => false,
                    'posted_at' => $newDate
                ]),

                // News category
                'news_unread' => Post::factory()->create([
                    'source_id' => $source2->id,
                    'category_id' => $category2->id,
                    'read' => false,
                    'posted_at' => $newDate
                ]),
            ]
        ];
    }

    /** @test */
    public function requires_authentication()
    {
        $response = $this->patchJson('/api/v1/posts/mark-all-read', [
            'read' => true
        ]);

        $response->assertStatus(401);
    }

    /** @test */
    public function can_mark_all_posts_as_read()
    {
        $user = $this->actingUser();
        $data = $this->createTestData();

        $response = $this->actingAs($user)
            ->patchJson('/api/v1/posts/mark-all-read', [
                'read' => true
            ]);

        $response->assertOk()
            ->assertJsonStructure([
                'message',
                'data' => ['updated_count', 'read_status', 'filters_applied']
            ]);

        // Should have marked 3 unread posts as read
        // (tech_old_unread, tech_new_unread, news_unread)
        $response->assertJson([
            'data' => [
                'updated_count' => 3,
                'read_status' => true
            ]
        ]);

        // Verify all posts are now read
        $this->assertEquals(4, Post::where('read', true)->count());
        $this->assertEquals(0, Post::where('read', false)->count());
    }

    /** @test */
    public function can_mark_all_posts_as_unread()
    {
        $user = $this->actingUser();
        $data = $this->createTestData();

        $response = $this->actingAs($user)
            ->patchJson('/api/v1/posts/mark-all-read', [
                'read' => false
            ]);

        $response->assertOk();

        // Should have marked 1 read post as unread (tech_old_read)
        $response->assertJson([
            'data' => [
                'updated_count' => 1,
                'read_status' => false
            ]
        ]);

        // Verify all posts are now unread
        $this->assertEquals(0, Post::where('read', true)->count());
        $this->assertEquals(4, Post::where('read', false)->count());
    }

    /** @test */
    public function can_filter_by_source()
    {
        $user = $this->actingUser();
        $data = $this->createTestData();

        $response = $this->actingAs($user)
            ->patchJson('/api/v1/posts/mark-all-read', [
                'read' => true,
                'source_id' => $data['source1']->id
            ]);

        $response->assertOk();

        // Should only mark posts from source1 (tech posts)
        $response->assertJson([
            'data' => [
                'updated_count' => 2, // tech_old_unread + tech_new_unread
                'read_status' => true,
                'filters_applied' => [
                    'source_id' => $data['source1']->id
                ]
            ]
        ]);

        // Verify: tech posts are read, news post still unread
        $this->assertTrue($data['posts']['tech_old_unread']->fresh()->read);
        $this->assertTrue($data['posts']['tech_new_unread']->fresh()->read);
        $this->assertFalse($data['posts']['news_unread']->fresh()->read);
    }

    /** @test */
    public function can_filter_by_category()
    {
        $user = $this->actingUser();
        $data = $this->createTestData();

        $response = $this->actingAs($user)
            ->patchJson('/api/v1/posts/mark-all-read', [
                'read' => true,
                'category_id' => $data['category1']->id
            ]);

        $response->assertOk();

        // Should only mark posts from category1 (tech category)
        $response->assertJson([
            'data' => [
                'updated_count' => 2,
                'read_status' => true
            ]
        ]);

        // Verify correct posts were updated
        $this->assertTrue($data['posts']['tech_old_unread']->fresh()->read);
        $this->assertTrue($data['posts']['tech_new_unread']->fresh()->read);
        $this->assertFalse($data['posts']['news_unread']->fresh()->read);
    }

    /** @test */
    public function can_filter_by_date()
    {
        $user = $this->actingUser();
        $data = $this->createTestData();

        // Mark all posts older than 3 days as read
        $cutoffDate = Carbon::now()->subDays(3)->format('Y-m-d H:i:s');

        $response = $this->actingAs($user)
            ->patchJson('/api/v1/posts/mark-all-read', [
                'read' => true,
                'posted_before' => $cutoffDate
            ]);

        $response->assertOk();

        // Should only mark old posts (tech_old_unread)
        $response->assertJson([
            'data' => [
                'updated_count' => 1,
                'read_status' => true
            ]
        ]);

        // Verify only old unread post was marked as read
        $this->assertTrue($data['posts']['tech_old_unread']->fresh()->read);
        $this->assertFalse($data['posts']['tech_new_unread']->fresh()->read);
        $this->assertFalse($data['posts']['news_unread']->fresh()->read);
    }

    /** @test */
    public function can_combine_filters()
    {
        $user = $this->actingUser();
        $data = $this->createTestData();

        // Mark posts in source1 that are older than 3 days
        $cutoffDate = Carbon::now()->subDays(3)->format('Y-m-d H:i:s');

        $response = $this->actingAs($user)
            ->patchJson('/api/v1/posts/mark-all-read', [
                'read' => true,
                'source_id' => $data['source1']->id,
                'posted_before' => $cutoffDate
            ]);

        $response->assertOk();

        $response->assertJson([
            'data' => [
                'updated_count' => 1, // Only tech_old_unread matches both filters
                'read_status' => true,
                'filters_applied' => [
                    'source_id' => $data['source1']->id,
                    'posted_before' => $cutoffDate
                ]
            ]
        ]);
    }

    /** @test */
    public function validates_required_read_status()
    {
        $user = $this->actingUser();

        $response = $this->actingAs($user)
            ->patchJson('/api/v1/posts/mark-all-read', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['read']);
    }

    /** @test */
    public function validates_source_exists()
    {
        $user = $this->actingUser();

        $response = $this->actingAs($user)
            ->patchJson('/api/v1/posts/mark-all-read', [
                'read' => true,
                'source_id' => 99999
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['source_id']);
    }

    /** @test */
    public function validates_category_exists()
    {
        $user = $this->actingUser();

        $response = $this->actingAs($user)
            ->patchJson('/api/v1/posts/mark-all-read', [
                'read' => true,
                'category_id' => 99999
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['category_id']);
    }

    /** @test */
    public function validates_date_format()
    {
        $user = $this->actingUser();

        $response = $this->actingAs($user)
            ->patchJson('/api/v1/posts/mark-all-read', [
                'read' => true,
                'posted_before' => 'invalid-date'
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['posted_before']);
    }

    /** @test */
    public function is_efficient_only_updates_relevant_posts()
    {
        // This test verifies that we only update posts that need updating
        $user = $this->actingUser();
        $data = $this->createTestData();

        // All posts are already in their target state except for unread ones
        $response = $this->actingAs($user)
            ->patchJson('/api/v1/posts/mark-all-read', [
                'read' => true
            ]);

        // Should only update the 3 unread posts, not the 1 already read
        $response->assertJson([
            'data' => ['updated_count' => 3]
        ]);
    }
}
