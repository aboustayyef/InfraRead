<?php

namespace Tests\Feature\Api\V1;

use App\Models\Category;
use App\Models\Post;
use App\Models\Source;
use App\Models\User;
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

        $response = $this->actingAs($user)->postJson('/api/v1/posts/'.$post->id.'/summary', [
            'sentences' => 2,
        ]);
        $response->assertOk()
            ->assertJsonStructure(['data' => ['post_id','sentences','summary']]);
        $this->assertStringContainsString('Sentence one', $response->json('data.summary'));
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

        $response = $this->actingAs($user)->postJson('/api/v1/posts/'.$post->id.'/summary');
        $response->assertStatus(502)
            ->assertJsonStructure(['error']);
    }
}
