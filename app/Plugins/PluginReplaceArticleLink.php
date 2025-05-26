<?php

namespace App\Plugins;

use App\Models\Post;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Symfony\Component\DomCrawler\Crawler;

/**********************************************************************************
 * All plugins take a App/Post object, transform it and return true if succesful
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
 * Post->content;
 * Post->url
 */

class PluginReplaceArticleLink implements PluginInterface
{
    private $post;

    public function __construct(Post $post)
    {
        $this->post = $post;
    }

    public function handle()
    {
                try {
            $client = new Client();
            $response = $client->request('GET', $this->post->url);
            $html = (string) $response->getBody();
            $crawler = new Crawler($html);

            $articleLinks = $crawler->filter('article a');
            foreach ($articleLinks as $key => $link) {
                $href = $link->getAttribute('href');
                if (!str_contains($href, 'slashdot') && !str_contains($href, 'linkedin.com/in/beauhd')) {
                    $this->post->url = $href;
                    $this->post->save();
                    break;
                }
            }

            return true;
        } catch (\Exception $e) {
            return false;
        }

    }
}
