<?php

namespace App\Exceptions\FeedProcessing;

use App\Models\Source;
use Exception;

/**
 * Exception thrown when plugin processing fails.
 *
 * This covers errors during post-processing steps like content transformation,
 * relative link fixing, text legibility improvements, etc.
 *
 * Plugin errors usually shouldn't prevent the post from being saved,
 * but we want to track them for debugging.
 */
class PluginException extends FeedProcessingException
{
    protected string $pluginName;

    /**
     * Create a new plugin exception.
     *
     * @param string $message Human-readable error description
     * @param Source $source The source being processed
     * @param string $pluginName Name of the plugin that failed
     * @param array $context Additional context (post ID, plugin config, etc.)
     * @param Exception|null $previous Original exception from plugin
     */
    public function __construct(
        string $message,
        Source $source,
        string $pluginName,
        array $context = [],
        ?Exception $previous = null
    ) {
        $this->pluginName = $pluginName;

        // Add plugin-specific context
        $context = array_merge([
            'plugin_name' => $pluginName,
            'error_type' => 'plugin_failure'
        ], $context);

        $pluginMessage = "Plugin '{$pluginName}' failed: {$message}";

        parent::__construct($pluginMessage, $source, $context, $previous);
    }

    /**
     * Get the name of the plugin that failed.
     */
    public function getPluginName(): string
    {
        return $this->pluginName;
    }

    /**
     * {@inheritdoc}
     */
    public function getErrorType(): string
    {
        return 'plugin_failure';
    }

    /**
     * {@inheritdoc}
     *
     * Plugin errors are usually not retryable at the individual post level
     * since they indicate code/logic issues rather than temporary failures.
     * However, we might retry the entire source processing.
     */
    public function isRetryable(): bool
    {
        return false; // Plugin logic errors don't usually fix themselves
    }

    /**
     * Create exception for plugin configuration errors.
     */
    public static function configurationError(
        Source $source,
        string $pluginName,
        string $configIssue
    ): self {
        return new self(
            "Configuration error: {$configIssue}",
            $source,
            $pluginName,
            ['config_error' => $configIssue]
        );
    }

    /**
     * Create exception for plugin execution errors.
     */
    public static function executionError(
        Source $source,
        string $pluginName,
        string $errorMessage,
        ?int $postId = null
    ): self {
        $context = ['execution_error' => $errorMessage];
        if ($postId) {
            $context['post_id'] = $postId;
        }

        return new self(
            "Execution failed: {$errorMessage}",
            $source,
            $pluginName,
            $context
        );
    }
}
