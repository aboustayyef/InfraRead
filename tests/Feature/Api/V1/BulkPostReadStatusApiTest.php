<?php

namespace Tests\Feature\Api\V1;

use App\Models\Category;
use App\Models\Post;
use App\Models\Source;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class BulkPostReadStatusApiTest extends TestCase
{
    use RefreshDatabase;

    protected function actingUser(): User
    {
        return User::factory()->create();
    }

    protected function createPosts(int $count, array $attributes = []): array
    {
        $category = Category::factory()->create();
        $source = Source::factory()->create(['category_id' => $category->id]);

        $posts = [];
        for ($i = 0; $i < $count; $i++) {
            $posts[] = Post::factory()->create(array_merge([
                'source_id' => $source->id,
                'category_id' => $category->id,
                'read' => false
            ], $attributes));
        }

        return $posts;
    }

    #[Test]
    public function requires_authentication()
    {
        $posts = $this->createPosts(2);

        $response = $this->patchJson('/api/v1/posts/bulk-read-status', [
            'post_ids' => [$posts[0]->id, $posts[1]->id],
            'read' => true
        ]);

        $response->assertStatus(401);
    }

    #[Test]
    public function can_bulk_mark_posts_as_read()
    {
        $user = $this->actingUser();
        $posts = $this->createPosts(3, ['read' => false]);
        $postIds = array_map(fn($post) => $post->id, $posts);

        $response = $this->actingAs($user)
            ->patchJson('/api/v1/posts/bulk-read-status', [
                'post_ids' => $postIds,
                'read' => true
            ]);

        $response->assertOk()
            ->assertJsonStructure([
                'message',
                'data' => ['updated_count', 'read_status']
            ])
            ->assertJson([
                'data' => [
                    'updated_count' => 3,
                    'read_status' => true
                ]
            ]);

        // Verify all posts were updated
        foreach ($posts as $post) {
            $this->assertTrue($post->fresh()->read);
        }
    }

    #[Test]
    public function can_bulk_mark_posts_as_unread()
    {
        $user = $this->actingUser();
        $posts = $this->createPosts(2, ['read' => true]);
        $postIds = array_map(fn($post) => $post->id, $posts);

        $response = $this->actingAs($user)
            ->patchJson('/api/v1/posts/bulk-read-status', [
                'post_ids' => $postIds,
                'read' => false
            ]);

        $response->assertOk()
            ->assertJson([
                'data' => [
                    'updated_count' => 2,
                    'read_status' => false
                ]
            ]);

        foreach ($posts as $post) {
            $this->assertFalse($post->fresh()->read);
        }
    }

    #[Test]
    public function validates_post_ids_are_required()
    {
        $user = $this->actingUser();

        $response = $this->actingAs($user)
            ->patchJson('/api/v1/posts/bulk-read-status', [
                'read' => true
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['post_ids']);
    }

    #[Test]
    public function validates_post_ids_is_array()
    {
        $user = $this->actingUser();

        $response = $this->actingAs($user)
            ->patchJson('/api/v1/posts/bulk-read-status', [
                'post_ids' => 'not-an-array',
                'read' => true
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['post_ids']);
    }

    #[Test]
    public function validates_post_ids_not_empty()
    {
        $user = $this->actingUser();

        $response = $this->actingAs($user)
            ->patchJson('/api/v1/posts/bulk-read-status', [
                'post_ids' => [],
                'read' => true
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['post_ids']);
    }

    #[Test]
    public function validates_each_post_id_is_integer()
    {
        $user = $this->actingUser();

        $response = $this->actingAs($user)
            ->patchJson('/api/v1/posts/bulk-read-status', [
                'post_ids' => [1, 'invalid', 3],
                'read' => true
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['post_ids.1']);
    }

    #[Test]
    public function validates_read_status_is_required()
    {
        $user = $this->actingUser();
        $posts = $this->createPosts(1);

        $response = $this->actingAs($user)
            ->patchJson('/api/v1/posts/bulk-read-status', [
                'post_ids' => [$posts[0]->id]
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['read']);
    }

    #[Test]
    public function fails_when_some_posts_do_not_exist()
    {
        $user = $this->actingUser();
        $posts = $this->createPosts(2);

        $response = $this->actingAs($user)
            ->patchJson('/api/v1/posts/bulk-read-status', [
                'post_ids' => [$posts[0]->id, 99999, $posts[1]->id],
                'read' => true
            ]);

        $response->assertStatus(422)
            ->assertJson([
                'message' => 'Some posts were not found'
            ]);

        // Verify no posts were updated (transaction rollback)
        foreach ($posts as $post) {
            $this->assertFalse($post->fresh()->read);
        }
    }

    #[Test]
    public function enforces_maximum_posts_limit()
    {
        $user = $this->actingUser();

        // Create array with 1001 IDs (exceeds limit of 1000)
        $tooManyIds = range(1, 1001);

        $response = $this->actingAs($user)
            ->patchJson('/api/v1/posts/bulk-read-status', [
                'post_ids' => $tooManyIds,
                'read' => true
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['post_ids']);
    }

    #[Test]
    public function is_idempotent()
    {
        // Test that calling the same operation multiple times
        // produces the same result

        $user = $this->actingUser();
        $posts = $this->createPosts(2, ['read' => false]);
        $postIds = array_map(fn($post) => $post->id, $posts);

        // First call
        $response1 = $this->actingAs($user)
            ->patchJson('/api/v1/posts/bulk-read-status', [
                'post_ids' => $postIds,
                'read' => true
            ]);

        // Second call with same data
        $response2 = $this->actingAs($user)
            ->patchJson('/api/v1/posts/bulk-read-status', [
                'post_ids' => $postIds,
                'read' => true
            ]);

        // Both should succeed
        $response1->assertOk();
        $response2->assertOk();

        // Posts should still be read
        foreach ($posts as $post) {
            $this->assertTrue($post->fresh()->read);
        }
    }
}
