<?php

namespace App\Plugins;

/**
 * Interface for all InfraRead plugins.
 *
 * Plugins are classes that modify Post objects based on their source.
 * They can transform content, mark posts as read, fix formatting issues,
 * or perform any other post-processing operations.
 */
interface PluginInterface
{
    /**
     * Execute the plugin logic.
     *
     * This method should perform the plugin's main functionality,
     * such as modifying the post content, changing post properties,
     * or performing external operations.
     *
     * @return bool True if the plugin executed successfully, false otherwise
     */
    public function handle(): bool;

    /**
     * Get plugin metadata and description.
     *
     * This method should return information about what the plugin does,
     * what properties it modifies, and any configuration options it supports.
     *
     * @return array Plugin metadata including name, description, and options
     */
    public function getMetadata(): array;
}
