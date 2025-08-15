<?php

namespace App\Plugins;

use App\Models\Post;

/**********************************************************************************
 * FixRelativeLinks Plugin
 **********************************************************************************
 *
 * About This Plugin
 * -------------------
 * Converts relative links and images to absolute URLs using the post's domain.
 * This ensures that images and links work correctly when viewed outside the
 * original website context.
 *
 * Modified Properties:
 * --------------------
 * - Post->content (converts relative URLs to absolute)
 *
 * Supported Options:
 * ------------------
 * - convert_images: bool (default: true) - Convert relative image URLs
 * - convert_links: bool (default: false) - Convert relative link URLs
 * - remove_srcset: bool (default: true) - Remove srcset attributes
 */
class PluginFixRelativeLinks implements PluginInterface
{
    private Post $post;
    private array $options;

    /**
     * Create a new FixRelativeLinks plugin instance.
     *
     * @param Post $post The post to process
     * @param array $options Plugin configuration options
     */
    public function __construct(Post $post, array $options = [])
    {
        $this->post = $post;
        $this->options = array_merge($this->getDefaultOptions(), $options);
    }

    /**
     * Execute the plugin logic to fix relative links.
     *
     * @return bool True if successful, false if an error occurred
     */
    public function handle(): bool
    {
        try {
            $domain = $this->retrieveDomain($this->post->url);

            if (!$domain) {
                throw new \Exception('Could not extract domain from post URL: ' . $this->post->url);
            }

            $originalContent = $this->post->content;
            $modifiedContent = $originalContent;

            // Remove srcset attributes if configured
            if ($this->options['remove_srcset']) {
                $modifiedContent = $this->removeSrcsetAttributes($modifiedContent);
            }

            // Convert relative image URLs if configured
            if ($this->options['convert_images']) {
                $modifiedContent = $this->convertRelativeImages($modifiedContent, $domain);
            }

            // Convert relative link URLs if configured
            if ($this->options['convert_links']) {
                $modifiedContent = $this->convertRelativeLinks($modifiedContent, $domain);
            }

            // Only save if content was actually modified
            if ($modifiedContent !== $originalContent) {
                $this->post->content = $modifiedContent;
                $this->post->save();
            }

            return true;

        } catch (\Exception $e) {
            // Log the specific error for debugging
            \Log::error('FixRelativeLinks plugin failed', [
                'post_id' => $this->post->id,
                'post_url' => $this->post->url,
                'error' => $e->getMessage(),
                'options' => $this->options
            ]);

            return false;
        }
    }

    /**
     * Get plugin metadata and description.
     *
     * @return array Plugin metadata
     */
    public function getMetadata(): array
    {
        return [
            'name' => 'Fix Relative Links',
            'description' => 'Converts relative links and images to absolute URLs using the post\'s domain',
            'version' => '2.0.0',
            'author' => 'InfraRead',
            'modifies' => ['content'],
            'options' => [
                'convert_images' => [
                    'type' => 'boolean',
                    'default' => true,
                    'description' => 'Convert relative image URLs to absolute'
                ],
                'convert_links' => [
                    'type' => 'boolean',
                    'default' => false,
                    'description' => 'Convert relative link URLs to absolute'
                ],
                'remove_srcset' => [
                    'type' => 'boolean',
                    'default' => true,
                    'description' => 'Remove srcset attributes from images'
                ]
            ]
        ];
    }

    /**
     * Get default plugin options.
     *
     * @return array Default options
     */
    private function getDefaultOptions(): array
    {
        return [
            'convert_images' => true,
            'convert_links' => false,
            'remove_srcset' => true
        ];
    }

    /**
     * Extract the domain from a URL.
     *
     * @param string $url The URL to extract domain from
     * @return string|null The domain (scheme + host) or null if invalid
     */
    private function retrieveDomain(string $url): ?string
    {
        $parsedUrl = parse_url($url);

        if (!isset($parsedUrl['scheme']) || !isset($parsedUrl['host'])) {
            return null;
        }

        return $parsedUrl['scheme'] . '://' . $parsedUrl['host'];
    }

    /**
     * Remove srcset attributes from images.
     *
     * @param string $htmlContent The HTML content to process
     * @return string Modified HTML content
     */
    private function removeSrcsetAttributes(string $htmlContent): string
    {
        return preg_replace('/\s*srcset="[^"]*"/i', '', $htmlContent);
    }

    /**
     * Convert relative image URLs to absolute URLs.
     *
     * @param string $htmlContent The HTML content to process
     * @param string $domain The base domain to use
     * @return string Modified HTML content
     */
    private function convertRelativeImages(string $htmlContent, string $domain): string
    {
        // Convert <img src="/... to absolute URLs
        $pattern = '/<img([^>]*)\ssrc="\/([^"]*)"([^>]*)>/i';
        $replacement = '<img$1 src="' . rtrim($domain, '/') . '/$2"$3>';

        return preg_replace($pattern, $replacement, $htmlContent);
    }

    /**
     * Convert relative link URLs to absolute URLs.
     *
     * @param string $htmlContent The HTML content to process
     * @param string $domain The base domain to use
     * @return string Modified HTML content
     */
    private function convertRelativeLinks(string $htmlContent, string $domain): string
    {
        // Convert <a href="/... to absolute URLs
        $pattern = '/<a([^>]*)\shref="\/([^"]*)"([^>]*)>/i';
        $replacement = '<a$1 href="' . rtrim($domain, '/') . '/$2"$3>';

        return preg_replace($pattern, $replacement, $htmlContent);
    }
}
