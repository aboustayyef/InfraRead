<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AppTokenTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Ensure the app view uses the configured API token when set.
     */
    public function test_app_uses_configured_api_token(): void
    {
        $user = User::factory()->create();
        config(['infraread.api_token' => 'test-token']);

        $response = $this->actingAs($user)->get('/app');

        $response->assertOk();
        $response->assertViewHas('api_token', 'test-token');
    }
}
