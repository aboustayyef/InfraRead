<?php

namespace App\Plugins;

use App\Models\Post;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Symfony\Component\DomCrawler\Crawler;

/**********************************************************************************
 * All plugins take a App/Post object, transform it and return true if successful
 **********************************************************************************
 *
 * About This Plugin
 * -------------------
 * This plugin replaces the link of the article with that of the
 * first link in the body text. Useful for sites like slashdot that
 * curate content and linked to them
 *
 * Modified Properties:
 * --------------------
 * Post->url - Replaced with first external link found in article
 */

class PluginReplaceArticleLink implements PluginInterface
{
    private $post;
    private $options;

    public function __construct(Post $post, array $options = [])
    {
        $this->post = $post;
        $this->options = $options;
    }

    public function handle(): bool
    {
        try {
            $client = new Client();
            $response = $client->request('GET', $this->post->url);
            $html = (string) $response->getBody();
            $crawler = new Crawler($html);

            // Get exclusions from options or use defaults
            $exclusions = $this->options['exclusions'] ?? ['slashdot', 'linkedin.com/in/beauhd'];
            $selector = $this->options['selector'] ?? 'article a';

            $articleLinks = $crawler->filter($selector);
            foreach ($articleLinks as $key => $link) {
                $href = $link->getAttribute('href');

                $excluded = false;
                foreach ($exclusions as $exclusion) {
                    if (str_contains($href, $exclusion)) {
                        $excluded = true;
                        break;
                    }
                }

                if (!$excluded) {
                    $this->post->url = $href;
                    $this->post->save();
                    break;
                }
            }

            return true;
        } catch (\Exception $e) {
            Log::warning('PluginReplaceArticleLink failed', [
                'post_id' => $this->post->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    public function getMetadata(): array
    {
        return [
            'name' => 'Replace Article Link',
            'description' => 'Replaces post URL with first external link found in article content',
            'version' => '1.1.0',
            'author' => 'InfraRead',
            'modifies' => ['url'],
            'options' => [
                'exclusions' => 'Array of strings to exclude from link replacement',
                'selector' => 'CSS selector for finding links (default: "article a")'
            ]
        ];
    }
}
