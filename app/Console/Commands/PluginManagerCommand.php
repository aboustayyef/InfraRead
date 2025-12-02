<?php

namespace App\Console\Commands;

use App\Plugins\Kernel;
use App\Models\Source;
use App\Models\Post;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Plugin management and validation command.
 *
 * This command provides tools for managing the plugin system,
 * including validation, testing, and configuration inspection.
 */
class PluginManagerCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'plugins:manage
                           {action : Action to perform (list, validate, test, sources)}
                           {--source= : Source ID for testing}
                           {--post= : Post ID for testing}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Manage and validate the plugin system';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $action = $this->argument('action');

        return match ($action) {
            'list' => $this->listPlugins(),
            'validate' => $this->validateConfiguration(),
            'test' => $this->testPlugins(),
            'sources' => $this->showSourceMappings(),
            default => $this->error("Unknown action: {$action}. Available: list, validate, test, sources") ?: 1
        };
    }

    /**
     * List all available plugins with their metadata.
     */
    protected function listPlugins(): int
    {
        $kernel = new Kernel();
        $available = $kernel->getAvailablePlugins();

        $this->info('📦 Available Plugins');
        $this->info('==================');

        if (empty($available)) {
            $this->warn('No plugins found.');
            return 0;
        }

        foreach ($available as $pluginName) {
            $this->line("• {$pluginName}");

            try {
                // Try to get metadata if possible
                $className = "App\\Plugins\\Plugin{$pluginName}";
                if (class_exists($className)) {
                    $reflection = new \ReflectionClass($className);
                    if ($reflection->hasMethod('getMetadata')) {
                        // Create a dummy post for metadata (not ideal but necessary)
                        $dummyPost = new Post();
                        $plugin = new $className($dummyPost);
                        $metadata = $plugin->getMetadata();

                        $this->line("  Description: {$metadata['description']}");
                        $this->line("  Version: {$metadata['version']}");

                        if (!empty($metadata['options'])) {
                            $this->line("  Options: " . implode(', ', array_keys($metadata['options'])));
                        }
                    }
                }
            } catch (\Exception $e) {
                $this->line("  (Metadata unavailable: {$e->getMessage()})");
            }

            $this->line('');
        }

        return 0;
    }

    /**
     * Validate the plugin configuration.
     */
    protected function validateConfiguration(): int
    {
        $kernel = new Kernel();
        $errors = $kernel->validateConfiguration();

        $this->info('🔍 Plugin Configuration Validation');
        $this->info('==================================');

        if (empty($errors)) {
            $this->info('✅ All plugin configurations are valid!');
            return 0;
        }

        $this->error('❌ Configuration errors found:');
        foreach ($errors as $error) {
            $this->error("  • {$error}");
        }

        return 1;
    }

    /**
     * Test plugin execution on a specific source or post.
     */
    protected function testPlugins(): int
    {
        $sourceId = $this->option('source');
        $postId = $this->option('post');

        if ($postId) {
            return $this->testPostPlugins($postId);
        }

        if ($sourceId) {
            return $this->testSourcePlugins($sourceId);
        }

        $this->error('Please specify either --source=ID or --post=ID for testing');
        return 1;
    }

    /**
     * Test plugins for a specific post.
     */
    protected function testPostPlugins(int $postId): int
    {
        $post = Post::find($postId);

        if (!$post) {
            $this->error("Post {$postId} not found");
            return 1;
        }

        $this->info("🧪 Testing plugins for post: {$post->title}");
        $this->info("Source: {$post->source->name}");
        $this->info("URL: {$post->url}");
        $this->line('');

        // Get plugins for this post's source
        $kernel = new Kernel();
        $plugins = $kernel->getPluginsForSource($post->source);

        if (empty($plugins)) {
            $this->info('No plugins configured for this source.');
            return 0;
        }

        $this->info('Configured plugins:');
        foreach ($plugins as $pluginConfig) {
            $this->line("  • {$pluginConfig['name']}");
            if (!empty($pluginConfig['options'])) {
                $this->line("    Options: " . json_encode($pluginConfig['options']));
            }
        }

        $this->line('');
        $this->info('🚀 Executing plugins...');

        try {
            $post->applyPlugins();
            $this->info('✅ Plugin execution completed successfully');

        } catch (\Exception $e) {
            $this->error("❌ Plugin execution failed: {$e->getMessage()}");
            return 1;
        }

        return 0;
    }

    /**
     * Test plugins for all posts in a source.
     */
    protected function testSourcePlugins(int $sourceId): int
    {
        $source = Source::find($sourceId);

        if (!$source) {
            $this->error("Source {$sourceId} not found");
            return 1;
        }

        $this->info("🧪 Testing plugins for source: {$source->name}");

        $kernel = new Kernel();
        $plugins = $kernel->getPluginsForSource($source);

        if (empty($plugins)) {
            $this->info('No plugins configured for this source.');
            return 0;
        }

        $this->info('Configured plugins:');
        foreach ($plugins as $pluginConfig) {
            $this->line("  • {$pluginConfig['name']}");
        }

        // Fetch directly via query to avoid cached collection limitations on Source::posts()
        $posts = \App\Models\Post::where('source_id', $source->id)
            ->orderBy('posted_at', 'desc')
            ->limit(5)
            ->get();

        if ($posts->isEmpty()) {
            $this->warn('No posts found for this source.');
            return 0;
        }

        $this->line('');
        $this->info("🚀 Testing on {$posts->count()} recent posts...");

        $successCount = 0;
        $failCount = 0;

        foreach ($posts as $post) {
            try {
                $post->applyPlugins();
                $this->line("✅ {$post->title}");
                $successCount++;

            } catch (\Exception $e) {
                $this->line("❌ {$post->title}: {$e->getMessage()}");
                $failCount++;
            }
        }

        $this->line('');
        $this->info("📊 Results: {$successCount} successful, {$failCount} failed");

        return $failCount > 0 ? 1 : 0;
    }

    /**
     * Show plugin mappings for all sources.
     */
    protected function showSourceMappings(): int
    {
        $this->info('🗺️  Plugin-to-Source Mappings');
        $this->info('=============================');

        $kernel = new Kernel();
        $sources = Source::all();

        if ($sources->isEmpty()) {
            $this->warn('No sources found.');
            return 0;
        }

        $configuredCount = 0;
        $unconfiguredCount = 0;

        foreach ($sources as $source) {
            $plugins = $kernel->getPluginsForSource($source);

            if (!empty($plugins)) {
                $this->info("📰 {$source->name}");
                $this->line("   URL: {$source->url}");
                $this->line("   Plugins:");

                foreach ($plugins as $pluginConfig) {
                    $optionsStr = !empty($pluginConfig['options'])
                        ? ' (' . json_encode($pluginConfig['options']) . ')'
                        : '';
                    $this->line("     • {$pluginConfig['name']}{$optionsStr}");
                }

                $this->line('');
                $configuredCount++;
            } else {
                $unconfiguredCount++;
            }
        }

        if ($unconfiguredCount > 0) {
            $this->comment("📝 {$unconfiguredCount} sources have no plugins configured");
        }

        $this->line('');
        $this->info("📊 Summary: {$configuredCount} configured, {$unconfiguredCount} unconfigured");

        return 0;
    }
}
