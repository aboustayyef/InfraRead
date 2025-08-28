<?php

namespace Tests\Feature\Api\V1;

use App\Models\Category;
use App\Models\Post;
use App\Models\Source;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow(now());
    }

    protected function actingUser()
    {
        return User::factory()->create();
    }

    public function test_requires_authentication()
    {
        $response = $this->getJson('/api/v1/posts');
        $response->assertStatus(401); // Unauthorized JSON
    }

    public function test_returns_paginated_posts()
    {
        $user = $this->actingUser();
        $category = Category::factory()->create();
        $source = Source::factory()->create(['category_id' => $category->id]);
        Post::factory()->count(3)->create(['source_id' => $source->id, 'category_id' => $category->id]);

        $response = $this->actingAs($user)->getJson('/api/v1/posts');
        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id','title','url','excerpt','posted_at','read','uid','author','time_ago']
                ],
                'meta' => ['total','current_page','last_page','per_page']
            ]);
    }

    public function test_can_filter_by_source()
    {
        $user = $this->actingUser();
        $category = Category::factory()->create();
        $sourceA = Source::factory()->create(['category_id' => $category->id]);
        $sourceB = Source::factory()->create(['category_id' => $category->id]);
        Post::factory()->count(2)->create(['source_id' => $sourceA->id, 'category_id' => $category->id]);
        Post::factory()->count(1)->create(['source_id' => $sourceB->id, 'category_id' => $category->id]);

        $response = $this->actingAs($user)->getJson('/api/v1/posts?filter[source]='.$sourceA->id);
        $response->assertOk();
        $this->assertCount(2, $response->json('data'));
    }

    public function test_can_filter_by_category()
    {
        $user = $this->actingUser();
        $categoryA = Category::factory()->create();
        $categoryB = Category::factory()->create();
        $sourceA = Source::factory()->create(['category_id' => $categoryA->id]);
        $sourceB = Source::factory()->create(['category_id' => $categoryB->id]);
        Post::factory()->count(2)->create(['source_id' => $sourceA->id, 'category_id' => $categoryA->id]);
        Post::factory()->count(1)->create(['source_id' => $sourceB->id, 'category_id' => $categoryB->id]);

        $response = $this->actingAs($user)->getJson('/api/v1/posts?filter[category]='.$categoryA->id);
        $response->assertOk();
        $this->assertCount(2, $response->json('data'));
    }

    public function test_include_relations()
    {
        $user = $this->actingUser();
        $category = Category::factory()->create();
        $source = Source::factory()->create(['category_id' => $category->id]);
        $post = Post::factory()->create(['source_id' => $source->id, 'category_id' => $category->id]);

        $response = $this->actingAs($user)->getJson('/api/v1/posts?include=source,category');
        $response->assertOk();
        $first = $response->json('data.0');
        $this->assertNotNull($first['source']);
        $this->assertNotNull($first['category']);
    }
}
