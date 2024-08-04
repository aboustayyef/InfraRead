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
        $dom = new \DOMDocument;

        // Suppress errors due to malformed HTML and load the HTML content
        // Wrap the content with a proper HTML structure
        @$dom->loadHTML('<!DOCTYPE html><html><body>' . $htmlContent . '</body></html>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

        // Create a new XPath object
        $xpath = new \DOMXPath($dom);

        // Find all <a> elements with href attribute
        $links = $xpath->query("//a[@href]");
        foreach ($links as $link) {
            $href = $link->getAttribute('href');
            if (parse_url($href, PHP_URL_SCHEME) === null) { // If the href is relative
                $link->setAttribute('href', rtrim($domain, '/') . '/' . ltrim($href, '/'));
            }
        }

        // Find all <img> elements with src and srcset attributes
        $images = $xpath->query("//img[@src]");
        foreach ($images as $img) {
            $src = $img->getAttribute('src');
            if (parse_url($src, PHP_URL_SCHEME) === null) { // If the src is relative
                $img->setAttribute('src', rtrim($domain, '/') . '/' . ltrim($src, '/'));
            }

            // Handle srcset attribute
            $srcset = $img->getAttribute('srcset');
            if ($srcset) {
                $srcsetParts = explode(',', $srcset);
                $absoluteSrcsetParts = [];
                foreach ($srcsetParts as $part) {
                    $urlDescriptor = preg_split('/\s+/', trim($part));
                    $url = $urlDescriptor[0];
                    if (parse_url($url, PHP_URL_SCHEME) === null) { // If the url is relative
                        $url = rtrim($domain, '/') . '/' . ltrim($url, '/');
                    }
                    $absoluteSrcsetParts[] = $url . (isset($urlDescriptor[1]) ? ' ' . $urlDescriptor[1] : '');
                }
                $img->setAttribute('srcset', implode(', ', $absoluteSrcsetParts));
            }
        }

        // Return the modified HTML content (extract the body content)
        $bodyContent = $dom->saveHTML($dom->getElementsByTagName('body')->item(0));
        return substr($bodyContent, 6, -7); // Remove the enclosing <body> tags
    }
}
