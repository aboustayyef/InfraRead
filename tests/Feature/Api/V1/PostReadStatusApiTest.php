<?php

namespace Tests\Feature\Api\V1;

use App\Models\Category;
use App\Models\Post;
use App\Models\Source;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostReadStatusApiTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test helper: Create a user for authentication
     *
     * We'll use this pattern in multiple tests to avoid repetition.
     * This is called the "Factory Pattern" - creating test objects
     * in a consistent way.
     */
    protected function actingUser(): User
    {
        return User::factory()->create();
    }

    /**
     * Test helper: Create a complete post with relationships
     *
     * Posts need both a source and category, so we create
     * the full object graph here.
     */
    protected function createPost(array $attributes = []): Post
    {
        $category = Category::factory()->create();
        $source = Source::factory()->create(['category_id' => $category->id]);

        return Post::factory()->create(array_merge([
            'source_id' => $source->id,
            'category_id' => $category->id,
            'read' => false  // Default to unread
        ], $attributes));
    }

    /** @test */
    public function requires_authentication()
    {
        // Arrange: Create a post
        $post = $this->createPost();

        // Act: Try to update without authentication
        $response = $this->patchJson("/api/v1/posts/{$post->id}/read-status", [
            'read' => true
        ]);

        // Assert: Should be unauthorized
        $response->assertStatus(401);
    }

    /** @test */
    public function can_mark_post_as_read()
    {
        // Arrange: Create user and unread post
        $user = $this->actingUser();
        $post = $this->createPost(['read' => false]);

        // Act: Mark as read
        $response = $this->actingAs($user)
            ->patchJson("/api/v1/posts/{$post->id}/read-status", [
                'read' => true
            ]);

        // Assert: Success response and database updated
        $response->assertOk()
            ->assertJsonStructure([
                'message',
                'data' => [
                    'id', 'title', 'read', 'url'
                ]
            ])
            ->assertJson([
                'data' => ['read' => true]
            ]);

        // Verify database was actually updated
        $this->assertTrue($post->fresh()->read);
    }

    /** @test */
    public function can_mark_post_as_unread()
    {
        // Arrange: Create user and read post
        $user = $this->actingUser();
        $post = $this->createPost(['read' => true]);

        // Act: Mark as unread
        $response = $this->actingAs($user)
            ->patchJson("/api/v1/posts/{$post->id}/read-status", [
                'read' => false
            ]);

        // Assert: Success response and database updated
        $response->assertOk()
            ->assertJson([
                'data' => ['read' => false]
            ]);

        $this->assertFalse($post->fresh()->read);
    }

    /** @test */
    public function validates_read_status_is_required()
    {
        $user = $this->actingUser();
        $post = $this->createPost();

        $response = $this->actingAs($user)
            ->patchJson("/api/v1/posts/{$post->id}/read-status", []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['read']);
    }

    /** @test */
    public function validates_read_status_is_boolean()
    {
        $user = $this->actingUser();
        $post = $this->createPost();

        $response = $this->actingAs($user)
            ->patchJson("/api/v1/posts/{$post->id}/read-status", [
                'read' => 'invalid'
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['read']);
    }

    /** @test */
    public function returns_404_for_nonexistent_post()
    {
        $user = $this->actingUser();

        $response = $this->actingAs($user)
            ->patchJson("/api/v1/posts/99999/read-status", [
                'read' => true
            ]);

        $response->assertStatus(404);
    }

    /** @test */
    public function is_idempotent()
    {
        // Idempotent means: calling the same operation multiple times
        // should have the same result as calling it once.

        $user = $this->actingUser();
        $post = $this->createPost(['read' => false]);

        // Mark as read twice
        $this->actingAs($user)
            ->patchJson("/api/v1/posts/{$post->id}/read-status", ['read' => true]);

        $response = $this->actingAs($user)
            ->patchJson("/api/v1/posts/{$post->id}/read-status", ['read' => true]);

        // Should still succeed and post should still be read
        $response->assertOk();
        $this->assertTrue($post->fresh()->read);
    }
}
