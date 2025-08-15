<?php

namespace App\Plugins;

use App\Models\Source;

/**
 * Plugin Kernel - Manages plugin configuration and assignment to sources.
 *
 * This class handles the mapping of plugins to sources and provides
 * a flexible configuration system for plugin options and execution order.
 */
class Kernel
{
    /**
     * Get the legacy plugin configuration (for backward compatibility).
     *
     * @deprecated Use getPluginsForSource() for new implementations
     * @return array Legacy plugin configuration
     */
    public function get()
    {
        // Legacy format for backward compatibility
        return [
            'httpskottkeorg' => ['FixRelativeLinks'],
            'httpsslashdotorg' => ['MakeTextLegible', 'ReplaceArticleLink'],
            'httpswwwmacstoriesnet' => ['MarkPostAsRead'],
            'httpswwwcaseylisscom' => ['MarkPostAsRead'],
            'httpswwwslowboringcom' => ['MarkPostAsRead'],
        ];
    }

    /**
     * Get plugins configured for a specific source.
     *
     * This method returns plugin configurations in the new structured format,
     * including plugin options and proper ordering. Falls back to legacy
     * configuration for existing sources.
     *
     * @param Source $source The source to get plugins for
     * @return array Array of plugin configurations with name and options
     */
    public function getPluginsForSource(Source $source): array
    {
        // Try new configuration format first
        $newConfig = $this->getNewPluginConfiguration();
        $sourceKey = $this->generateSourceKey($source);

        if (isset($newConfig[$sourceKey])) {
            return $newConfig[$sourceKey];
        }

        // Fall back to legacy configuration for existing sources
        $legacyConfig = $this->get();
        $legacyKey = $source->shortname();

        if (isset($legacyConfig[$legacyKey])) {
            // Convert legacy format to new format
            return $this->convertLegacyToNew($legacyConfig[$legacyKey]);
        }

        return []; // No plugins configured for this source
    }

    /**
     * Get the new structured plugin configuration.
     *
     * This configuration format supports plugin options, conditional execution,
     * and more flexible source matching.
     *
     * @return array New plugin configuration structure
     */
    private function getNewPluginConfiguration(): array
    {
        return [
            // Domain-based configuration (more reliable than shortname)
            'kottke.org' => [
                [
                    'name' => 'FixRelativeLinks',
                    'options' => [
                        'convert_images' => true,
                        'convert_links' => false
                    ]
                ]
            ],

            'slashdot.org' => [
                [
                    'name' => 'MakeTextLegible',
                    'options' => [
                        'remove_ads' => true,
                        'clean_formatting' => true
                    ]
                ],
                [
                    'name' => 'ReplaceArticleLink',
                    'options' => [
                        'follow_redirects' => false
                    ]
                ]
            ],

            'macstories.net' => [
                [
                    'name' => 'MarkPostAsRead',
                    'options' => [
                        'reason' => 'Auto-read for MacStories'
                    ]
                ]
            ],

            'www.caseyliss.com' => [
                [
                    'name' => 'MarkPostAsRead',
                    'options' => [
                        'reason' => 'Auto-read for Casey Liss'
                    ]
                ]
            ],

            'www.slowboring.com' => [
                [
                    'name' => 'MarkPostAsRead',
                    'options' => [
                        'reason' => 'Auto-read for Slow Boring'
                    ]
                ]
            ]
        ];
    }

    /**
     * Generate a source key for plugin configuration lookup.
     *
     * This method creates a consistent key based on the source URL domain,
     * providing more reliable matching than shortnames.
     *
     * @param Source $source
     * @return string Source key for configuration lookup
     */
    private function generateSourceKey(Source $source): string
    {
        // Extract domain from source URL for more reliable matching
        $parsed = parse_url($source->url);
        $domain = $parsed['host'] ?? '';

        // Remove www. prefix for cleaner matching
        return str_replace('www.', '', strtolower($domain));
    }

    /**
     * Convert legacy plugin configuration to new format.
     *
     * @param array $legacyPlugins Array of plugin names
     * @return array New format plugin configuration
     */
    private function convertLegacyToNew(array $legacyPlugins): array
    {
        $converted = [];

        foreach ($legacyPlugins as $pluginName) {
            $converted[] = [
                'name' => $pluginName,
                'options' => [] // No options in legacy format
            ];
        }

        return $converted;
    }

    /**
     * Get all available plugin classes.
     *
     * This method scans the Plugins directory and returns all available
     * plugin classes that implement the PluginInterface.
     *
     * @return array Available plugin classes
     */
    public function getAvailablePlugins(): array
    {
        $pluginDir = __DIR__;
        $plugins = [];

        foreach (glob($pluginDir . '/Plugin*.php') as $file) {
            $className = basename($file, '.php');
            $fullClassName = __NAMESPACE__ . '\\' . $className;

            // Skip if not a proper plugin class
            if ($className === 'PluginInterface' || !class_exists($fullClassName)) {
                continue;
            }

            // Check if implements interface
            if (is_subclass_of($fullClassName, PluginInterface::class)) {
                $plugins[] = str_replace('Plugin', '', $className);
            }
        }

        return $plugins;
    }

    /**
     * Validate plugin configuration.
     *
     * This method checks if all configured plugins exist and are properly
     * implemented.
     *
     * @return array Validation results with errors
     */
    public function validateConfiguration(): array
    {
        $errors = [];
        $availablePlugins = $this->getAvailablePlugins();
        $config = $this->getNewPluginConfiguration();

        foreach ($config as $sourceKey => $plugins) {
            foreach ($plugins as $pluginConfig) {
                $pluginName = $pluginConfig['name'];

                if (!in_array($pluginName, $availablePlugins)) {
                    $errors[] = "Plugin '{$pluginName}' configured for '{$sourceKey}' does not exist or is not properly implemented";
                }
            }
        }

        return $errors;
    }
}
