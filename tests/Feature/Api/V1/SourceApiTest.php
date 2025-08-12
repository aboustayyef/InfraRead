<?php

namespace Tests\Feature\Api\V1;

use App\Models\Category;
use App\Models\Source;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SourceApiTest extends TestCase
{
    use RefreshDatabase;

    protected function actingUser()
    {
        return User::factory()->create();
    }

    public function test_requires_authentication()
    {
        $response = $this->getJson('/api/v1/sources');
        $response->assertStatus(401);
    }

    public function test_lists_sources_with_optional_category_include()
    {
        $user = $this->actingUser();
        $category = Category::factory()->create();
        Source::factory()->count(2)->create(['category_id' => $category->id]);

        $response = $this->actingAs($user)->getJson('/api/v1/sources?include=category');
        $response->assertOk();
        $this->assertCount(2, $response->json('data'));
        $this->assertNotNull($response->json('data.0.category'));
    }
}
