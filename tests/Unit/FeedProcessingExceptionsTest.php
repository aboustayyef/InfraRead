<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Source;
use App\Models\Category;
use App\Exceptions\FeedProcessing\FeedFetchException;
use App\Exceptions\FeedProcessing\FeedParseException;
use App\Exceptions\FeedProcessing\PluginException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;

/**
 * Test suite for custom feed processing exceptions.
 *
 * This ensures our custom exceptions work correctly, provide appropriate
 * context, and handle retryability logic properly.
 */
class FeedProcessingExceptionsTest extends TestCase
{
    use RefreshDatabase;

    protected Source $source;

    protected function setUp(): void
    {
        parent::setUp();

        $category = Category::factory()->create(['description' => 'Test Category']);
        $this->source = Source::factory()->create([
            'name' => 'Test Source',
            'category_id' => $category->id,
            'fetcher_source' => 'https://example.com/feed.xml'
        ]);
    }

    #[Test]
    public function feed_fetch_exception_includes_source_context()
    {
        $exception = new FeedFetchException(
            'Network timeout',
            $this->source,
            ['timeout_seconds' => 30]
        );

        $this->assertSame($this->source, $exception->getSource());
        $this->assertEquals('fetch_failure', $exception->getErrorType());
        $this->assertArrayHasKey('timeout_seconds', $exception->getContext());
        $this->assertEquals(30, $exception->getContext()['timeout_seconds']);
        $this->assertStringContainsString('Test Source', $exception->getMessage());
        $this->assertStringContainsString('Network timeout', $exception->getMessage());
    }

    #[Test]
    public function feed_fetch_http_error_factory_method_works()
    {
        $exception = FeedFetchException::httpError($this->source, 404, 'Not Found');

        $this->assertStringContainsString('HTTP 404 error', $exception->getMessage());
        $this->assertEquals(404, $exception->getContext()['http_status']);
        $this->assertEquals('Not Found', $exception->getContext()['response_body']);
        $this->assertFalse($exception->isRetryable()); // 404 should not be retried
    }

    #[Test]
    public function feed_fetch_timeout_factory_method_works()
    {
        $exception = FeedFetchException::timeout($this->source, 30);

        $this->assertStringContainsString('timed out after 30 seconds', $exception->getMessage());
        $this->assertEquals(30, $exception->getContext()['timeout_seconds']);
        $this->assertTrue($exception->isRetryable()); // Timeouts should be retried
    }

    #[Test]
    public function feed_fetch_invalid_url_factory_method_works()
    {
        $badUrl = 'not-a-valid-url';
        $exception = FeedFetchException::invalidUrl($this->source, $badUrl);

        $this->assertStringContainsString('Invalid or malformed URL', $exception->getMessage());
        $this->assertEquals($badUrl, $exception->getContext()['invalid_url']);
        $this->assertFalse($exception->isRetryable()); // Invalid URLs shouldn't be retried
    }

    #[Test]
    public function feed_fetch_retryability_logic_works_correctly()
    {
        // Test permanent errors (not retryable)
        $permanentErrors = [400, 401, 403, 404, 410];
        foreach ($permanentErrors as $status) {
            $exception = FeedFetchException::httpError($this->source, $status);
            $this->assertFalse($exception->isRetryable(), "HTTP {$status} should not be retryable");
        }

        // Test temporary errors (retryable)
        $temporaryErrors = [500, 502, 503, 429];
        foreach ($temporaryErrors as $status) {
            $exception = FeedFetchException::httpError($this->source, $status);
            $this->assertTrue($exception->isRetryable(), "HTTP {$status} should be retryable");
        }

        // Test no HTTP status (network errors - retryable)
        $exception = FeedFetchException::timeout($this->source, 30);
        $this->assertTrue($exception->isRetryable());
    }

    #[Test]
    public function feed_parse_exception_works_correctly()
    {
        $exception = new FeedParseException(
            'Invalid XML structure',
            $this->source,
            ['xml_error' => 'Unexpected end of file']
        );

        $this->assertEquals('parse_failure', $exception->getErrorType());
        $this->assertTrue($exception->isRetryable()); // Parse errors might be temporary
        $this->assertStringContainsString('Invalid XML structure', $exception->getMessage());
    }

    #[Test]
    public function feed_parse_xml_error_factory_method_works()
    {
        $xmlError = 'Syntax error at line 5';
        $feedContent = '<rss><channel><item>';

        $exception = FeedParseException::xmlError($this->source, $xmlError, $feedContent);

        $this->assertStringContainsString('XML parsing failed', $exception->getMessage());
        $this->assertEquals($xmlError, $exception->getContext()['xml_error']);
        $this->assertEquals('<rss><channel><item>', $exception->getContext()['feed_snippet']);
    }

    #[Test]
    public function feed_parse_missing_elements_factory_method_works()
    {
        $missingElements = ['title', 'description', 'link'];
        $exception = FeedParseException::missingElements($this->source, $missingElements);

        $this->assertStringContainsString('missing required elements', $exception->getMessage());
        $this->assertEquals($missingElements, $exception->getContext()['missing_elements']);
    }

    #[Test]
    public function feed_parse_unsupported_format_factory_method_works()
    {
        $exception = FeedParseException::unsupportedFormat($this->source, 'atom');

        $this->assertStringContainsString('Unsupported feed format: atom', $exception->getMessage());
        $this->assertEquals('atom', $exception->getContext()['detected_format']);
    }

    #[Test]
    public function feed_parse_empty_feed_factory_method_works()
    {
        $exception = FeedParseException::emptyFeed($this->source);

        $this->assertStringContainsString('contains no items', $exception->getMessage());
        $this->assertEquals(0, $exception->getContext()['item_count']);
    }

    #[Test]
    public function plugin_exception_includes_plugin_context()
    {
        $pluginName = 'FixRelativeLinks';
        $exception = new PluginException(
            'Failed to process images',
            $this->source,
            $pluginName,
            ['post_id' => 123]
        );

        $this->assertEquals($pluginName, $exception->getPluginName());
        $this->assertEquals('plugin_failure', $exception->getErrorType());
        $this->assertFalse($exception->isRetryable()); // Plugin errors usually aren't retryable
        $this->assertStringContainsString("Plugin 'FixRelativeLinks' failed", $exception->getMessage());
        $this->assertEquals(123, $exception->getContext()['post_id']);
    }

    #[Test]
    public function plugin_configuration_error_factory_method_works()
    {
        $exception = PluginException::configurationError(
            $this->source,
            'TestPlugin',
            'Missing required config parameter'
        );

        $this->assertStringContainsString('Configuration error', $exception->getMessage());
        $this->assertEquals('Missing required config parameter', $exception->getContext()['config_error']);
    }

    #[Test]
    public function plugin_execution_error_factory_method_works()
    {
        $exception = PluginException::executionError(
            $this->source,
            'TestPlugin',
            'Division by zero',
            456
        );

        $this->assertStringContainsString('Execution failed', $exception->getMessage());
        $this->assertEquals('Division by zero', $exception->getContext()['execution_error']);
        $this->assertEquals(456, $exception->getContext()['post_id']);
    }

    #[Test]
    public function all_exceptions_include_source_information_in_message()
    {
        $exceptions = [
            FeedFetchException::httpError($this->source, 500),
            FeedParseException::xmlError($this->source, 'Test error'),
            PluginException::executionError($this->source, 'TestPlugin', 'Test error')
        ];

        foreach ($exceptions as $exception) {
            $this->assertStringContainsString('Test Source', $exception->getMessage());
            $this->assertStringContainsString((string) $this->source->id, $exception->getMessage());
        }
    }

    #[Test]
    public function exceptions_preserve_original_exception_chain()
    {
        $originalException = new \Exception('Original network error');

        $exception = new FeedFetchException(
            'Network problem',
            $this->source,
            [],
            $originalException
        );

        $this->assertSame($originalException, $exception->getPrevious());
    }
}
