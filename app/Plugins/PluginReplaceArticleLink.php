<?php

namespace App\Plugins;
use \App\Post;
use GuzzleHttp\Client;
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
    function __construct(Post $post)
    {
        $this->post = $post;
    }

    function handle(){
        try {
            $client = new Client();
            $response = $client->request('GET', $this->post->url);
            $html = (string) $response->getBody();
            $crawler = new Crawler($html);

            $articleLinks = $crawler->filter('article a');
            foreach ($articleLinks as $key => $link) {
                if (!str_contains($link->getAttribute('href'), 'slashdot')) {
                    $this->post->url = $link->getAttribute('href');
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