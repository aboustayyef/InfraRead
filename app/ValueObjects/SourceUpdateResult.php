<?php

namespace App\ValueObjects;

/**
 * Value object representing the result of a source update operation.
 *
 * This provides a structured way to return both success and failure information
 * from feed processing operations, making it easier to handle results consistently
 * across the application.
 */
class SourceUpdateResult
{
    public function __construct(
        public readonly bool $success,
        public readonly int $postsProcessed,
        public readonly float $durationSeconds,
        public readonly ?string $errorMessage = null,
        public readonly ?string $errorType = null,
        public readonly array $context = []
    ) {}

    /**
     * Create a successful result.
     */
    public static function success(
        int $postsProcessed,
        float $durationSeconds,
        array $context = []
    ): self {
        return new self(
            success: true,
            postsProcessed: $postsProcessed,
            durationSeconds: $durationSeconds,
            context: $context
        );
    }

    /**
     * Create a failed result.
     */
    public static function failure(
        float $durationSeconds,
        string $errorMessage,
        string $errorType,
        array $context = []
    ): self {
        return new self(
            success: false,
            postsProcessed: 0,
            durationSeconds: $durationSeconds,
            errorMessage: $errorMessage,
            errorType: $errorType,
            context: $context
        );
    }

    /**
     * Get a human-readable summary of the result.
     */
    public function getSummary(): string
    {
        if ($this->success) {
            return sprintf(
                'Successfully processed %d posts in %.2f seconds',
                $this->postsProcessed,
                $this->durationSeconds
            );
        }

        return sprintf(
            'Failed after %.2f seconds: %s',
            $this->durationSeconds,
            $this->errorMessage
        );
    }

    /**
     * Get structured data for API responses.
     */
    public function toArray(): array
    {
        return [
            'success' => $this->success,
            'posts_processed' => $this->postsProcessed,
            'duration_seconds' => $this->durationSeconds,
            'error_message' => $this->errorMessage,
            'error_type' => $this->errorType,
            'context' => $this->context
        ];
    }

    /**
     * Check if this represents a successful operation.
     */
    public function isSuccess(): bool
    {
        return $this->success;
    }

    /**
     * Check if this represents a failed operation.
     */
    public function isFailure(): bool
    {
        return !$this->success;
    }
}
