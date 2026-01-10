<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Source;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class OnboardingTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function a_logged_in_user_with_no_sources_is_redirected_to_setup()
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->get('/');

        $response->assertRedirect('/setup');
    }

    #[Test]
    public function a_logged_in_user_with_sources_can_go_to_app()
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();
        $sources = Source::factory()->count(10)->create(['category_id' => $category->id]);

        $response = $this->actingAs($user)->get('/');
        $response->assertRedirect('/app');

    }
}
