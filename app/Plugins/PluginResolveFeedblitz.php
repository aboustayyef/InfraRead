<?php

namespace App\Plugins;

use App\Models\Post;
use Illuminate\Support\Facades\Http;

/**
 * Resolve FeedBlitz redirect URLs inside post content.
 *
 * Replaces any feeds.feedblitz.com links with their final, resolved URL
 * to avoid redirect hops when readers click links.
 */
class PluginResolveFeedblitz implements PluginInterface
{
    private Post $post;
    private array $options;

    public function __construct(Post $post, array $options = [])
    {
        $this->post = $post;
        $this->options = array_merge($this->getDefaultOptions(), $options);
    }

    public function handle(): bool
    {
        try {
            $content = $this->post->content;
            $pattern = '/https?:\/\/feeds\.feedblitz\.com\/[^\s"\'<>]*/i';

            if (!preg_match_all($pattern, $content, $matches)) {
                return true; // Nothing to do
            }

            $uniqueUrls = array_unique($matches[0]);
            $resolved = [];

            foreach ($uniqueUrls as $url) {
                if ($this->options['skip_utility_links'] && $this->isUtilityLink($url)) {
                    \Log::debug('FeedBlitz resolver: skipping utility link', [
                        'post_id' => $this->post->id ?? null,
                        'url' => $url,
                    ]);
                    $resolved[$url] = $url;
                    continue;
                }

                $resolved[$url] = $this->resolveFinalUrl($url);
            }

            $replacedContent = str_replace(array_keys($resolved), array_values($resolved), $content);

            if ($replacedContent !== $content) {
                $this->post->content = $replacedContent;
                $this->post->save();
            }

            return true;
        } catch (\Exception $e) {
            \Log::warning('PluginResolveFeedblitz failed', [
                'post_id' => $this->post->id ?? null,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    public function getMetadata(): array
    {
        return [
            'name' => 'Resolve FeedBlitz Redirects',
            'description' => 'Replaces feeds.feedblitz.com links with their final resolved URLs to avoid redirect hops.',
            'version' => '1.1.0',
            'author' => 'InfraRead',
            'modifies' => ['content'],
            'options' => [
                'timeout_seconds' => [
                    'type' => 'integer',
                    'default' => 5,
                    'description' => 'HTTP timeout per request'
                ],
                'max_redirects' => [
                    'type' => 'integer',
                    'default' => 5,
                    'description' => 'Maximum redirect hops to follow'
                ],
                'retries' => [
                    'type' => 'integer',
                    'default' => 1,
                    'description' => 'Number of retry attempts on failure'
                ],
                'skip_utility_links' => [
                    'type' => 'boolean',
                    'default' => true,
                    'description' => 'Skip pixels/share/subscribe utility links'
                ]
            ]
        ];
    }

    private function resolveFinalUrl(string $url): string
    {
        try {
            $response = Http::withHeaders([
                    'User-Agent' => 'InfraRead-Resolver/1.0 (+https://infraread.test)',
                ])
                ->timeout($this->options['timeout_seconds'])
                ->retry($this->options['retries'], 200)
                ->withOptions([
                    'allow_redirects' => [
                        'track_redirects' => true,
                        'max' => $this->options['max_redirects'],
                    ],
                ])
                ->get($url);

            $stats = $response->handlerStats();

            // Guzzle's handler stats include the final URL under the 'url' key.
            if (!empty($stats['url'])) {
                return $stats['url'];
            }

            // Fallback: if no stats, but request succeeded, keep original.
            return $url;
        } catch (\Exception $e) {
            \Log::info('FeedBlitz resolve failed; keeping original URL', [
                'url' => $url,
                'error' => $e->getMessage(),
            ]);

            return $url;
        }
    }

    private function isUtilityLink(string $url): bool
    {
        $path = strtolower(parse_url($url, PHP_URL_PATH) ?? '');

        // Pixels and share/subscribe utilities
        return str_contains($path, '/~/i/') // tracking pixel
            || str_starts_with($path, '/_/'); // share/utility endpoints
    }

    private function getDefaultOptions(): array
    {
        return [
            'timeout_seconds' => 5,
            'max_redirects' => 5,
            'retries' => 1,
            'skip_utility_links' => true,
        ];
    }
}
