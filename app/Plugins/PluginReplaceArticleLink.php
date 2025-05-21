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
        $uid = $this->post->uid;
        $uid = preg_replace('/-\d+$/', '', $uid);
        if ($uid !== "https://www.linkedin.com/in/beauhd/") {
            $this->post->url = $uid;
            $this->post->save();
        }
        return true;
    }
}
