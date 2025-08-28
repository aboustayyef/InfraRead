<?php

namespace App\Exceptions\FeedProcessing;

use Exception;
use App\Models\Source;

/**
 * Base exception class for all feed processing related errors.
 *
 * This provides a common interface for handling feed-related failures
 * and includes context about which source was being processed.
 */
abstract class FeedProcessingException extends Exception
{
    protected Source $source;
    protected array $context;

    /**
     * Create a new feed processing exception.
     *
     * @param string $message Human-readable error message
     * @param Source $source The source that was being processed when error occurred
     * @param array $context Additional context for debugging (URL, response code, etc.)
     * @param Exception|null $previous Previous exception in the chain
     */
    public function __construct(
        string $message,
        Source $source,
        array $context = [],
        ?Exception $previous = null
    ) {
        $this->source = $source;
        $this->context = $context;

        // Create detailed message with source context
        $fullMessage = sprintf(
            'Feed processing error for source "%s" (ID: %d): %s',
            $source->name,
            $source->id,
            $message
        );

        parent::__construct($fullMessage, 0, $previous);
    }

    /**
     * Get the source that was being processed.
     */
    public function getSource(): Source
    {
        return $this->source;
    }

    /**
     * Get additional context about the error.
     */
    public function getContext(): array
    {
        return $this->context;
    }

    /**
     * Get the error type for logging/categorization.
     */
    abstract public function getErrorType(): string;

    /**
     * Determine if this is a temporary error that should be retried.
     */
    abstract public function isRetryable(): bool;
}
