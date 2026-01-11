<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminTokenPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_token_page_shows_configured_env_token(): void
    {
        $user = User::factory()->create();
        $configuredToken = 'test-configured-token';

        config(['infraread.api_token' => $configuredToken]);

        $response = $this->actingAs($user)->get(route('admin.token.show'));

        $response->assertOk();
        $response->assertSee('INFRAREAD_API_TOKEN is set.');
        $response->assertSee($configuredToken);
    }

    public function test_admin_token_page_lists_tokens_newest_first(): void
    {
        $user = User::factory()->create();
        $olderToken = $user->createToken('older-token');
        $olderToken->accessToken->forceFill([
            'created_at' => now()->subDay(),
        ])->save();

        $newerToken = $user->createToken('newer-token');
        $newerToken->accessToken->forceFill([
            'created_at' => now(),
        ])->save();

        $response = $this->actingAs($user)->get(route('admin.token.show'));

        $response->assertOk();
        $response->assertSeeInOrder(['newer-token', 'older-token']);
    }
}
