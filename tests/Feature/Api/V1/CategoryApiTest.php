<?php

namespace Tests\Feature\Api\V1;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryApiTest extends TestCase
{
    use RefreshDatabase;

    protected function actingUser()
    {
        return User::factory()->create();
    }

    public function test_requires_authentication()
    {
        $response = $this->getJson('/api/v1/categories');
        $response->assertStatus(401);
    }

    public function test_lists_categories()
    {
        $user = $this->actingUser();
        Category::factory()->count(3)->create();

        $response = $this->actingAs($user)->getJson('/api/v1/categories');
        $response->assertOk();
        $this->assertCount(3, $response->json('data'));
    }
}
