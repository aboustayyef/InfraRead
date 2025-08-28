<?php

namespace App\Exceptions\FeedProcessing;

use App\Models\Source;
use Exception;

/**
 * Exception thrown when we successfully fetch the feed but cannot parse it.
 *
 * This indicates issues with the feed content itself - malformed XML,
 * invalid RSS structure, missing required fields, etc.
 *
 * Examples:
 * - Invalid XML syntax
 * - Missing RSS channel elements
 * - Unsupported feed format
 * - Encoding issues
 */
class FeedParseException extends FeedProcessingException
{
    /**
     * Create a new feed parse exception.
     *
     * @param string $message Human-readable error description
     * @param Source $source The source whose feed failed to parse
     * @param array $context Additional context (XML snippet, parser error, etc.)
     * @param Exception|null $previous Original parsing exception
     */
    public function __construct(
        string $message,
        Source $source,
        array $context = [],
        ?Exception $previous = null
    ) {
        // Add parse-specific context
        $context = array_merge([
            'url' => $source->fetcher_source,
            'error_type' => 'parse_failure'
        ], $context);

        parent::__construct($message, $source, $context, $previous);
    }

    /**
     * {@inheritdoc}
     */
    public function getErrorType(): string
    {
        return 'parse_failure';
    }

    /**
     * {@inheritdoc}
     *
     * Parse errors are usually permanent (malformed XML, bad feed structure)
     * but could be temporary if the feed publisher fixes the issue.
     * We retry a few times but not as aggressively as fetch errors.
     */
    public function isRetryable(): bool
    {
        // Some parse errors might be temporary (server sending partial response)
        // but most are permanent feed structure issues
        return true; // We'll let the exponential backoff handle the limiting
    }

    /**
     * Create exception for XML parsing errors.
     */
    public static function xmlError(Source $source, string $xmlError, string $feedContent = ''): self
    {
        return new self(
            "XML parsing failed: {$xmlError}",
            $source,
            [
                'xml_error' => $xmlError,
                'feed_snippet' => substr($feedContent, 0, 200) // First 200 chars for debugging
            ]
        );
    }

    /**
     * Create exception for missing required RSS elements.
     */
    public static function missingElements(Source $source, array $missingElements): self
    {
        $elements = implode(', ', $missingElements);

        return new self(
            "Feed is missing required elements: {$elements}",
            $source,
            ['missing_elements' => $missingElements]
        );
    }

    /**
     * Create exception for unsupported feed formats.
     */
    public static function unsupportedFormat(Source $source, string $detectedFormat = 'unknown'): self
    {
        return new self(
            "Unsupported feed format: {$detectedFormat}. Only RSS feeds are supported.",
            $source,
            ['detected_format' => $detectedFormat]
        );
    }

    /**
     * Create exception for empty feeds.
     */
    public static function emptyFeed(Source $source): self
    {
        return new self(
            "Feed contains no items or is empty",
            $source,
            ['item_count' => 0]
        );
    }
}
