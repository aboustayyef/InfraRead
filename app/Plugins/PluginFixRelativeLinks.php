<?php

namespace App\Plugins;

use App\Models\Post;

/**********************************************************************************
 * All plugins take a App/Post object, transform it and return true if succesful
 **********************************************************************************
 *
 * About This Plugin
 * -------------------
 * Clean Up Contents: Makes small improvements to content like replace relative links with absolute links for images.
 *
 * Modified Properties:
 * --------------------
 * Post->content
 */

class PluginFixRelativeLinks implements PluginInterface
{
    private $post;

    public function __construct(Post $post)
    {
        $this->post = $post;
    }

    public function handle()
    {
        try {
            $domain = $this->retrieveDomain($this->post->url);
            $this->post->content = $this->convertRelativeLinksToAbsolute($this->post->content, $domain );
            $this->post->save();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    private function retrieveDomain($url) {
        // Parse the URL and get its components
        $parsedUrl = parse_url($url);

        // Reconstruct the domain part (scheme + host)
        $domain = $parsedUrl['scheme'] . '://' . $parsedUrl['host'];

        return $domain;
    }
    private function convertRelativeLinksToAbsolute($htmlContent, $domain) {
        // Remove any srcset attributes
        $htmlContent = preg_replace('/\s*srcset="[^"]*"/i', '', $htmlContent);

        // Replace <img src="/... with the absolute domain
        $htmlContent = str_replace('<img src="/', '<img src="' . rtrim($domain, '/') . '/', $htmlContent);

        return $htmlContent;
    }



}
