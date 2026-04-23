<?php

namespace Tests\Feature\Api\V1;

use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Client\Request;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PostQuoteExplanationApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_requires_authentication(): void
    {
        $post = Post::factory()->create();

        $response = $this->postJson("/api/v1/posts/{$post->id}/quote-explanation", [
            'quote' => $this->longQuote(),
        ]);

        $response->assertStatus(401);
    }

    public function test_rejects_short_quotes_without_calling_openai(): void
    {
        Http::preventStrayRequests();
        $user = User::factory()->create();
        $post = Post::factory()->create();

        $response = $this->actingAs($user)
            ->postJson("/api/v1/posts/{$post->id}/quote-explanation", [
                'quote' => 'This quote is too short to explain with AI.',
            ]);

        $response->assertStatus(422);
        Http::assertNothingSent();
    }

    public function test_explains_large_quote_successfully(): void
    {
        Cache::flush();
        $user = User::factory()->create();
        $post = Post::factory()->create();

        Http::fake([
            'api.openai.com/*' => Http::response([
                'choices' => [
                    ['message' => ['content' => '<p>This study says younger households earned more, but growth slowed.</p>']],
                ],
            ], 200),
        ]);

        $response = $this->actingAs($user)
            ->postJson("/api/v1/posts/{$post->id}/quote-explanation", [
                'quote' => $this->longQuote(),
            ]);

        $response->assertOk()
            ->assertJsonPath('data.post_id', $post->id)
            ->assertJsonPath('data.cached', false)
            ->assertJsonPath('data.explanation', '<p>This study says younger households earned more, but growth slowed.</p>');

        Http::assertSent(function (Request $request) {
            $payload = $request->data();
            $messages = $payload['messages'] ?? [];
            $systemPrompt = $messages[0]['content'] ?? '';
            $userPrompt = $messages[1]['content'] ?? '';

            return $payload['model'] === 'gpt-4.1-mini'
                && $payload['max_tokens'] === 180
                && str_contains($systemPrompt, 'Return HTML with only <p>, <ul>, and <li> tags')
                && str_contains($userPrompt, 'Explain this quoted passage in simple language')
                && str_contains($userPrompt, 'Keep it under 120 words');
        });
    }

    public function test_reuses_cached_explanation_for_same_post_and_quote(): void
    {
        Cache::flush();
        $user = User::factory()->create();
        $post = Post::factory()->create();

        Http::fake([
            'api.openai.com/*' => Http::sequence()
                ->push([
                    'choices' => [
                        ['message' => ['content' => '<p>Cached explanation.</p>']],
                    ],
                ], 200),
        ]);

        $firstResponse = $this->actingAs($user)
            ->postJson("/api/v1/posts/{$post->id}/quote-explanation", [
                'quote' => $this->longQuote(),
            ]);

        $secondResponse = $this->actingAs($user)
            ->postJson("/api/v1/posts/{$post->id}/quote-explanation", [
                'quote' => $this->longQuote(),
            ]);

        $firstResponse->assertOk()->assertJsonPath('data.cached', false);
        $secondResponse->assertOk()
            ->assertJsonPath('data.cached', true)
            ->assertJsonPath('data.explanation', '<p>Cached explanation.</p>');

        Http::assertSentCount(1);
    }

    private function longQuote(): string
    {
        return str_repeat(
            'The study compares household income across generations and finds that some younger households have higher real incomes, but the pace of improvement has slowed because work hours and family support changed over time. ',
            8
        );
    }
}
