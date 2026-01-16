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
        $post = Post::factory()->create(['source_id' => $source->id, 'category_id' => $category->id]);

        Http::fake([
            'api.openai.com/*' => Http::response([
                'choices' => [
                    ['message' => ['content' => '<p>Sentence one.</p><p>Sentence two.</p>']]
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

            return str_contains($systemPrompt, 'blockquote')
                && str_contains($systemPrompt, 'only <p> tags')
                && str_contains($userPrompt, 'Do not include <blockquote> tags')
                && str_contains($userPrompt, 'Wrap each output sentence in a <p>');
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
