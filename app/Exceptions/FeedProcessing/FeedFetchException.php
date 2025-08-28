<?php

namespace App\Exceptions\FeedProcessing;

use App\Models\Source;
use Exception;

/**
 * Exception thrown when we cannot fetch the RSS feed from the remote server.
 *
 * This typically indicates network issues, invalid URLs, server errors,
 * or authentication problems. Most fetch errors are retryable.
 *
 * Examples:
 * - HTTP 500/502/503 errors (server temporarily down)
 * - Network timeouts
 * - DNS resolution failures
 * - SSL certificate issues
 */
class FeedFetchException extends FeedProcessingException
{
    /**
     * Create a new feed fetch exception.
     *
     * @param string $message Human-readable error description
     * @param Source $source The source that failed to fetch
     * @param array $context Additional context (HTTP status, response time, etc.)
     * @param Exception|null $previous Original exception that caused this
     */
    public function __construct(
        string $message,
        Source $source,
        array $context = [],
        ?Exception $previous = null
    ) {
        // Add fetch-specific context
        $context = array_merge([
            'url' => $source->fetcher_source,
            'error_type' => 'fetch_failure'
        ], $context);

        parent::__construct($message, $source, $context, $previous);
    }

    /**
     * {@inheritdoc}
     */
    public function getErrorType(): string
    {
        return 'fetch_failure';
    }

    /**
     * {@inheritdoc}
     *
     * Most fetch errors are temporary (network issues, server down, etc.)
     * Only HTTP 404 and similar permanent errors should not be retried.
     */
    public function isRetryable(): bool
    {
        $httpStatus = $this->context['http_status'] ?? null;

        // Don't retry on permanent HTTP errors
        $permanentErrors = [400, 401, 403, 404, 410];

        if ($httpStatus && in_array($httpStatus, $permanentErrors)) {
            return false;
        }

        // Retry on network issues, server errors, timeouts, etc.
        return true;
    }

    /**
     * Create exception for HTTP errors.
     */
    public static function httpError(Source $source, int $statusCode, string $responseBody = ''): self
    {
        return new self(
            "HTTP {$statusCode} error when fetching feed",
            $source,
            [
                'http_status' => $statusCode,
                'response_body' => substr($responseBody, 0, 500) // Limit for logging
            ]
        );
    }

    /**
     * Create exception for network timeouts.
     */
    public static function timeout(Source $source, int $timeoutSeconds): self
    {
        return new self(
            "Request timed out after {$timeoutSeconds} seconds",
            $source,
            ['timeout_seconds' => $timeoutSeconds]
        );
    }

    /**
     * Create exception for invalid URLs.
     */
    public static function invalidUrl(Source $source, string $url): self
    {
        return new self(
            "Invalid or malformed URL: {$url}",
            $source,
            [
                'invalid_url' => $url,
                'http_status' => 400 // Treat as bad request - not retryable
            ]
        );
    }
}
