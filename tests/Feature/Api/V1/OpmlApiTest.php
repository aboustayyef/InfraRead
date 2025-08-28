<?php

namespace Tests\Feature\Api\V1;

use App\Models\Category;
use App\Models\Source;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class OpmlApiTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        Sanctum::actingAs($this->user);
    }

    /** @test */
    public function it_can_export_opml_when_no_sources_exist()
    {
        $response = $this->getJson('/api/v1/export-opml');

        $response->assertOk()
            ->assertJsonStructure([
                'message',
                'data' => [
                    'content',
                    'filename',
                    'sources_count',
                    'categories_count'
                ]
            ])
            ->assertJson([
                'data' => [
                    'sources_count' => 0,
                    'categories_count' => 0
                ]
            ]);

        $opmlContent = $response->json('data.content');
        $this->assertStringContainsString('<?xml version="1.0" encoding="UTF-8"?>', $opmlContent);
        $this->assertStringContainsString('<opml version="2.0">', $opmlContent);
        $this->assertStringContainsString('<title>Infraread RSS Feeds</title>', $opmlContent);
    }

    /** @test */
    public function it_can_export_opml_with_sources_and_categories()
    {
        // Create test data
        $category1 = Category::factory()->create(['description' => 'Tech News']);
        $category2 = Category::factory()->create(['description' => 'Sports']);

        Source::factory()->create([
            'name' => 'TechCrunch',
            'fetcher_source' => 'https://techcrunch.com/feed/',
            'url' => 'https://techcrunch.com',
            'category_id' => $category1->id
        ]);

        Source::factory()->create([
            'name' => 'ESPN',
            'fetcher_source' => 'https://espn.com/feed/',
            'url' => 'https://espn.com',
            'category_id' => $category2->id
        ]);

        // Uncategorized source
        Source::factory()->create([
            'name' => 'Random Blog',
            'fetcher_source' => 'https://example.com/feed/',
            'url' => 'https://example.com',
            'category_id' => null
        ]);

        $response = $this->getJson('/api/v1/export-opml');

        $response->assertOk()
            ->assertJson([
                'data' => [
                    'sources_count' => 3,
                    'categories_count' => 2
                ]
            ]);

        $opmlContent = $response->json('data.content');
        $this->assertStringContainsString('Tech News', $opmlContent);
        $this->assertStringContainsString('Sports', $opmlContent);
        $this->assertStringContainsString('TechCrunch', $opmlContent);
        $this->assertStringContainsString('ESPN', $opmlContent);
        $this->assertStringContainsString('Random Blog', $opmlContent);
        $this->assertStringContainsString('https://techcrunch.com/feed/', $opmlContent);
    }

    /** @test */
    public function it_requires_authentication_for_opml_export()
    {
        // Remove Sanctum authentication
        app('auth')->forgetGuards();

        $response = $this->getJson('/api/v1/export-opml');

        $response->assertUnauthorized();
    }

    /** @test */
    public function it_can_preview_valid_opml_file()
    {
        $opmlContent = $this->getValidOpmlContent();
        $file = UploadedFile::fake()->createWithContent('test.opml', $opmlContent);

        $response = $this->postJson('/api/v1/preview-opml', [
            'opml' => $file
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'message',
                'data' => [
                    'categories',
                    'uncategorized_sources',
                    'total_categories',
                    'total_sources'
                ]
            ]);

        $data = $response->json('data');
        $this->assertEquals(1, $data['total_categories']);
        $this->assertEquals(2, $data['total_sources']);
        $this->assertEquals('Tech News', $data['categories'][0]['name']);
        $this->assertEquals(1, $data['categories'][0]['source_count']);
        $this->assertCount(1, $data['uncategorized_sources']);
        $this->assertEquals('Random Blog', $data['uncategorized_sources'][0]['name']);
    }

    /** @test */
    public function it_rejects_invalid_opml_file()
    {
        $invalidContent = '<invalid>not opml</invalid>';
        $file = UploadedFile::fake()->createWithContent('test.opml', $invalidContent);

        $response = $this->postJson('/api/v1/preview-opml', [
            'opml' => $file
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'message' => 'Failed to preview OPML',
            ]);
    }

    /** @test */
    public function it_validates_opml_file_requirements()
    {
        // Test missing file
        $response = $this->postJson('/api/v1/preview-opml', []);
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['opml']);

        // Test non-file
        $response = $this->postJson('/api/v1/preview-opml', [
            'opml' => 'not-a-file'
        ]);
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['opml']);

        // Test wrong file type
        $file = UploadedFile::fake()->create('test.pdf', 100);
        $response = $this->postJson('/api/v1/preview-opml', [
            'opml' => $file
        ]);
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['opml']);
    }

    /** @test */
    public function it_can_import_opml_in_replace_mode()
    {
        // Create existing data that should be replaced
        $existingCategory = Category::factory()->create();
        $existingSource = Source::factory()->create(['category_id' => $existingCategory->id]);

        $opmlContent = $this->getValidOpmlContent();
        $file = UploadedFile::fake()->createWithContent('test.opml', $opmlContent);

        $response = $this->postJson('/api/v1/import-opml', [
            'opml' => $file,
            'mode' => 'replace'
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'message',
                'data' => [
                    'mode',
                    'categories_created',
                    'sources_created',
                    'sources_skipped',
                    'errors'
                ]
            ]);

        $data = $response->json('data');
        $this->assertEquals('replace', $data['mode']);
        $this->assertGreaterThan(0, $data['categories_created']);
        $this->assertGreaterThan(0, $data['sources_created']);

        // Verify old data was replaced
        $this->assertDatabaseMissing('sources', [
            'name' => $existingSource->name,
            'fetcher_source' => $existingSource->fetcher_source
        ]);
        $this->assertDatabaseMissing('categories', [
            'description' => $existingCategory->description
        ]);

        // Verify new data exists
        $this->assertDatabaseHas('sources', ['name' => 'TechCrunch']);
        $this->assertDatabaseHas('categories', ['description' => 'Tech News']);
    }

    /** @test */
    public function it_can_import_opml_in_merge_mode()
    {
        // Create existing data that should be preserved
        $existingCategory = Category::factory()->create(['description' => 'Existing Category']);
        $existingSource = Source::factory()->create([
            'category_id' => $existingCategory->id,
            'fetcher_source' => 'https://existing.com/feed'
        ]);

        $opmlContent = $this->getValidOpmlContent();
        $file = UploadedFile::fake()->createWithContent('test.opml', $opmlContent);

        $response = $this->postJson('/api/v1/import-opml', [
            'opml' => $file,
            'mode' => 'merge'
        ]);

        $response->assertOk();

        $data = $response->json('data');
        $this->assertEquals('merge', $data['mode']);

        // Verify old data was preserved
        $this->assertDatabaseHas('sources', ['id' => $existingSource->id]);
        $this->assertDatabaseHas('categories', ['id' => $existingCategory->id]);

        // Verify new data was added
        $this->assertDatabaseHas('sources', ['name' => 'TechCrunch']);
        $this->assertDatabaseHas('categories', ['description' => 'Tech News']);
    }

    /** @test */
    public function it_skips_duplicate_sources_in_merge_mode()
    {
        // Create existing source with same RSS URL
        Category::factory()->create();
        Source::factory()->create([
            'fetcher_source' => 'https://techcrunch.com/feed/'
        ]);

        $opmlContent = $this->getValidOpmlContent();
        $file = UploadedFile::fake()->createWithContent('test.opml', $opmlContent);

        $response = $this->postJson('/api/v1/import-opml', [
            'opml' => $file,
            'mode' => 'merge'
        ]);

        $response->assertOk();

        $data = $response->json('data');
        $this->assertGreaterThan(0, $data['sources_skipped']);

        // Should only have one TechCrunch source
        $this->assertEquals(1, Source::where('fetcher_source', 'https://techcrunch.com/feed/')->count());
    }

    /** @test */
    public function it_defaults_to_replace_mode_when_mode_not_specified()
    {
        $opmlContent = $this->getValidOpmlContent();
        $file = UploadedFile::fake()->createWithContent('test.opml', $opmlContent);

        $response = $this->postJson('/api/v1/import-opml', [
            'opml' => $file
        ]);

        $response->assertOk();
        $this->assertEquals('replace', $response->json('data.mode'));
    }

    /** @test */
    public function it_requires_authentication_for_opml_import()
    {
        // Remove Sanctum authentication
        app('auth')->forgetGuards();

        $file = UploadedFile::fake()->create('test.opml');
        $response = $this->postJson('/api/v1/import-opml', ['opml' => $file]);

        $response->assertUnauthorized();
    }

    /**
     * Get valid OPML content for testing
     */
    private function getValidOpmlContent(): string
    {
        return '<?xml version="1.0" encoding="UTF-8"?>
<opml version="2.0">
    <head>
        <title>Test OPML</title>
    </head>
    <body>
        <outline text="Tech News" title="Tech News">
            <outline type="rss" text="TechCrunch" title="TechCrunch"
                     xmlUrl="https://techcrunch.com/feed/"
                     htmlUrl="https://techcrunch.com" />
        </outline>
        <outline type="rss" text="Random Blog" title="Random Blog"
                 xmlUrl="https://randomblog.com/feed/"
                 htmlUrl="https://randomblog.com" />
    </body>
</opml>';
    }
}
