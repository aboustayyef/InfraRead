<?php

namespace Tests\Feature;

use App\Models\User;
use App\Utilities\ApiTokenResolver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\PersonalAccessToken;
use Tests\TestCase;

class AppTokenTest extends TestCase
{
    use RefreshDatabase;

    public function test_app_uses_configured_api_token_when_matching_user(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('configured-token')->plainTextToken;
        config(['infraread.api_token' => $token]);

        $response = $this->actingAs($user)->get('/app');

        $response->assertOk();
        $response->assertViewHas('api_token', $token);
    }

    public function test_app_generates_token_when_configured_token_is_orphaned(): void
    {
        $oldUser = User::factory()->create();
        $oldToken = $oldUser->createToken('configured-token')->plainTextToken;
        config(['infraread.api_token' => $oldToken]);
        $oldUser->delete();

        $orphanedToken = PersonalAccessToken::findToken($oldToken);
        $this->assertNotNull($orphanedToken);
        $this->assertNull($orphanedToken->tokenable);

        $user = User::factory()->create();
        $resolvedToken = ApiTokenResolver::resolveForUser($user, 'spa-token');
        $resolvedTokenModel = PersonalAccessToken::findToken($resolvedToken);
        $this->assertNotNull($resolvedTokenModel);
        $this->assertSame($user->id, $resolvedTokenModel->tokenable_id);
    }
}
