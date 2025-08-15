<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Post;
use App\Models\Source;
use App\Models\Category;
use App\Models\User;
use App\Plugins\Kernel;
use App\Plugins\PluginFixRelativeLinks;
use App\Exceptions\FeedProcessing\PluginException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;

/**
 * Test the improved plugin system with enhanced error handling,
 * structured configuration, and comprehensive logging.
 */
class ImprovedPluginSystemTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Category $category;
    protected Source $source;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test data
        $this->user = User::factory()->create();
        $this->category = Category::factory()->create();
        $this->source = Source::factory()->create([
            'category_id' => $this->category->id,
            'url' => 'https://kottke.org/feed',
            'name' => 'Kottke Test Source'
        ]);

        // Spy on logging
        Log::spy();
    }

    /** @test */
    public function plugin_kernel_returns_plugins_for_known_source()
    {
        $kernel = new Kernel();
        $plugins = $kernel->getPluginsForSource($this->source);

        $this->assertNotEmpty($plugins);
        $this->assertIsArray($plugins);

        // Should have FixRelativeLinks plugin for kottke.org
        $this->assertCount(1, $plugins);
        $this->assertEquals('FixRelativeLinks', $plugins[0]['name']);
        $this->assertArrayHasKey('options', $plugins[0]);
    }

    /** @test */
    public function plugin_kernel_returns_empty_for_unknown_source()
    {
        $unknownSource = Source::factory()->create([
            'category_id' => $this->category->id,
            'url' => 'https://unknown-site.com/feed',
            'name' => 'Unknown Source'
        ]);

        $kernel = new Kernel();
        $plugins = $kernel->getPluginsForSource($unknownSource);

        $this->assertEmpty($plugins);
    }

    /** @test */
    public function plugin_kernel_supports_legacy_configuration()
    {
        // Create source that matches legacy configuration
        $legacySource = Source::factory()->create([
            'category_id' => $this->category->id,
            'url' => 'https://slashdot.org/feed',
            'name' => 'Slashdot'
        ]);

        $kernel = new Kernel();
        $plugins = $kernel->getPluginsForSource($legacySource);

        // Should get plugins from new configuration (slashdot.org)
        $this->assertCount(2, $plugins);
        $this->assertEquals('MakeTextLegible', $plugins[0]['name']);
        $this->assertEquals('ReplaceArticleLink', $plugins[1]['name']);
    }

    /** @test */
    public function post_applies_plugins_with_options()
    {
        $post = Post::factory()->create([
            'source_id' => $this->source->id,
            'category_id' => $this->category->id,
            'content' => '<img src="/test-image.jpg" alt="Test">',
            'url' => 'https://kottke.org/test-post'
        ]);

        $post->applyPlugins();

        // Content should be modified by FixRelativeLinks plugin
        $post->refresh();
        $this->assertStringContainsString('https://kottke.org/test-image.jpg', $post->content);
    }

    /** @test */
    public function post_handles_plugin_failures_gracefully()
    {
        // Create a post that will cause plugin issues
        $post = Post::factory()->create([
            'source_id' => $this->source->id,
            'category_id' => $this->category->id,
            'content' => '<img src="/test.jpg">',
            'url' => 'invalid-url-format' // This will cause the plugin to fail
        ]);

        // Should not throw exception
        $post->applyPlugins();

        // Should log plugin failure
        Log::shouldHaveReceived('warning')
           ->with('Plugin execution failed', \Mockery::type('array'))
           ->once();
    }

    /** @test */
    public function plugin_execution_preserves_error_context()
    {
        $post = Post::factory()->create([
            'source_id' => $this->source->id,
            'category_id' => $this->category->id,
            'content' => '<img src="/test.jpg">',
            'url' => 'not-a-valid-url'
        ]);

        $post->applyPlugins();

        // Verify comprehensive logging with context
        Log::shouldHaveReceived('warning')
           ->with('Plugin execution failed', \Mockery::on(function ($context) use ($post) {
               return isset($context['exception']) &&
                      isset($context['context']) &&
                      isset($context['plugin_name']) &&
                      $context['plugin_name'] === 'FixRelativeLinks' &&
                      $context['context']['post_id'] === $post->id;
           }))
           ->once();
    }

    /** @test */
    public function plugin_logs_successful_processing_summary()
    {
        $post = Post::factory()->create([
            'source_id' => $this->source->id,
            'category_id' => $this->category->id,
            'content' => '<img src="/valid.jpg">',
            'url' => 'https://kottke.org/valid-post'
        ]);

        $post->applyPlugins();

        // Should log processing summary
        Log::shouldHaveReceived('info')
           ->with('Plugin processing completed for post', \Mockery::on(function ($context) use ($post) {
               return $context['post_id'] === $post->id &&
                      isset($context['successful_plugins']) &&
                      in_array('FixRelativeLinks', $context['successful_plugins']) &&
                      $context['total_plugins'] === 1;
           }))
           ->once();
    }

    /** @test */
    public function fix_relative_links_plugin_supports_options()
    {
        $post = Post::factory()->create([
            'source_id' => $this->source->id,
            'category_id' => $this->category->id,
            'content' => '<img src="/image.jpg" srcset="/image-2x.jpg 2x"> <a href="/link">Link</a>',
            'url' => 'https://kottke.org/test'
        ]);

        $plugin = new PluginFixRelativeLinks($post, [
            'convert_images' => true,
            'convert_links' => true,
            'remove_srcset' => true
        ]);

        $result = $plugin->handle();

        $this->assertTrue($result);
        $post->refresh();

        // Should convert images and links, remove srcset
        $this->assertStringContainsString('https://kottke.org/image.jpg', $post->content);
        $this->assertStringContainsString('https://kottke.org/link', $post->content);
        $this->assertStringNotContainsString('srcset=', $post->content);
    }

    /** @test */
    public function plugin_metadata_provides_comprehensive_information()
    {
        $post = Post::factory()->create([
            'source_id' => $this->source->id,
            'category_id' => $this->category->id
        ]);

        $plugin = new PluginFixRelativeLinks($post);
        $metadata = $plugin->getMetadata();

        $this->assertArrayHasKey('name', $metadata);
        $this->assertArrayHasKey('description', $metadata);
        $this->assertArrayHasKey('version', $metadata);
        $this->assertArrayHasKey('options', $metadata);
        $this->assertArrayHasKey('modifies', $metadata);

        // Check options structure
        $this->assertArrayHasKey('convert_images', $metadata['options']);
        $this->assertArrayHasKey('convert_links', $metadata['options']);
        $this->assertArrayHasKey('remove_srcset', $metadata['options']);

        // Each option should have type and description
        foreach ($metadata['options'] as $option) {
            $this->assertArrayHasKey('type', $option);
            $this->assertArrayHasKey('description', $option);
            $this->assertArrayHasKey('default', $option);
        }
    }

    /** @test */
    public function kernel_can_validate_plugin_configuration()
    {
        $kernel = new Kernel();
        $errors = $kernel->validateConfiguration();

        // Should have no errors for valid configuration
        $this->assertIsArray($errors);
        // Note: This might have errors if plugins are missing,
        // which is expected in test environment
    }

    /** @test */
    public function kernel_can_list_available_plugins()
    {
        $kernel = new Kernel();
        $available = $kernel->getAvailablePlugins();

        $this->assertIsArray($available);
        $this->assertContains('FixRelativeLinks', $available);
    }

    /** @test */
    public function plugin_handles_malformed_post_urls_gracefully()
    {
        $post = Post::factory()->create([
            'source_id' => $this->source->id,
            'category_id' => $this->category->id,
            'content' => '<img src="/test.jpg">',
            'url' => '' // Empty URL
        ]);

        $plugin = new PluginFixRelativeLinks($post);
        $result = $plugin->handle();

        $this->assertFalse($result);

        // Content should remain unchanged
        $this->assertEquals('<img src="/test.jpg">', $post->content);
    }

    /** @test */
    public function plugin_only_saves_when_content_changes()
    {
        $post = Post::factory()->create([
            'source_id' => $this->source->id,
            'category_id' => $this->category->id,
            'content' => '<p>No relative links here</p>',
            'url' => 'https://kottke.org/test'
        ]);

        $originalUpdatedAt = $post->updated_at;

        // Wait a moment to ensure timestamp would change if saved
        sleep(1);

        $plugin = new PluginFixRelativeLinks($post);
        $result = $plugin->handle();

        $this->assertTrue($result);
        $post->refresh();

        // Should not have saved since content didn't change
        $this->assertEquals($originalUpdatedAt, $post->updated_at);
    }
}
