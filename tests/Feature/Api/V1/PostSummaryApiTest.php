<?php

namespace Tests\Feature\Api\V1;

use App\Models\Category;
use App\Models\Post;
use App\Models\Source;
use App\Models\User;
use Illuminate\Http\Client\Request;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PostSummaryApiTest extends TestCase
{
    use RefreshDatabase;

    protected function actingUser()
    {
        return User::factory()->create();
    }

    public function test_requires_authentication()
    {
        $post = Post::factory()->create();
        $response = $this->postJson('/api/v1/posts/'.$post->id.'/summary');
        $response->assertStatus(401);
    }

    public function test_generates_summary_successfully()
    {
        $user = $this->actingUser();
        $category = Category::factory()->create();
        $source = Source::factory()->create(['category_id' => $category->id]);
        $post = Post::factory()->create([
            'source_id' => $source->id,
            'category_id' => $category->id,
            'content' => '<p>The blogger explains why a small product decision matters for regular readers.</p>',
        ]);

        Http::fake([
            'api.openai.com/*' => Http::response([
                'choices' => [
                    ['message' => ['content' => '<p>Sentence one.</p>']]
                ]
            ], 200)
        ]);

        $csrf = csrf_token();
        $response = $this->withHeaders(['X-CSRF-TOKEN'=>$csrf])
            ->actingAs($user)
            ->postJson('/api/v1/posts/'.$post->id.'/summary', [
                'sentences' => 2,
            ]);
        $response->assertOk()
            ->assertJsonStructure(['data' => ['post_id','sentences','summary']]);
        $this->assertStringContainsString('Sentence one', $response->json('data.summary'));

        Http::assertSent(function (Request $request) {
            $payload = $request->data();
            $messages = $payload['messages'] ?? [];
            $systemPrompt = $messages[0]['content'] ?? '';
            $userPrompt = $messages[1]['content'] ?? '';

            return $payload['model'] === 'gpt-4.1-mini'
                && str_contains($systemPrompt, 'Do not attribute quoted claims to the blogger')
                && str_contains($systemPrompt, 'only <p>, <ul>, and <li> tags')
                && str_contains($userPrompt, 'Do not include <blockquote> tags')
                && str_contains($userPrompt, 'Quote profile: mostly original commentary')
                && str_contains($userPrompt, 'Article size: short')
                && str_contains($userPrompt, 'Return exactly one concise <p> paragraph');
        });
    }

    public function test_quote_heavy_large_posts_request_quote_aware_bullets()
    {
        $user = $this->actingUser();
        $category = Category::factory()->create();
        $source = Source::factory()->create(['category_id' => $category->id]);
        $post = Post::factory()->create([
            'source_id' => $source->id,
            'category_id' => $category->id,
            'content' => '<p>'.str_repeat('blogger framing ', 40).'</p><blockquote>'.str_repeat('quoted source point ', 1700).'</blockquote>',
        ]);

        Http::fake([
            'api.openai.com/*' => Http::response([
                'choices' => [
                    ['message' => ['content' => '<p>The blogger collects excerpts around one issue.</p><ul><li>First point.</li></ul>']]
                ]
            ], 200)
        ]);

        $csrf = csrf_token();
        $response = $this->withHeaders(['X-CSRF-TOKEN'=>$csrf])
            ->actingAs($user)
            ->postJson('/api/v1/posts/'.$post->id.'/summary', [
                'sentences' => 2,
            ]);

        $response->assertOk();

        Http::assertSent(function (Request $request) {
            $payload = $request->data();
            $messages = $payload['messages'] ?? [];
            $userPrompt = $messages[1]['content'] ?? '';

            return str_contains($userPrompt, 'Quote profile: mostly quoted material')
                && str_contains($userPrompt, 'Article size: large')
                && str_contains($userPrompt, 'followed by a <ul> list of 3 to 5 concise <li> main points')
                && $payload['max_tokens'] === 260;
        });
    }

    public function test_handles_summary_error()
    {
        $user = $this->actingUser();
        $category = Category::factory()->create();
        $source = Source::factory()->create(['category_id' => $category->id]);
        $post = Post::factory()->create(['source_id' => $source->id, 'category_id' => $category->id]);

        Http::fake([
            'api.openai.com/*' => Http::response('Server error', 500)
        ]);

        $csrf = csrf_token();
        $response = $this->withHeaders(['X-CSRF-TOKEN'=>$csrf])
            ->actingAs($user)
            ->postJson('/api/v1/posts/'.$post->id.'/summary');
        $response->assertStatus(502)
            ->assertJsonStructure(['error']);
    }
}
