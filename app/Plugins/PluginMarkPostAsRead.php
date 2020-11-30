<?php

namespace App\Plugins;

use App\Models\Post;

/**********************************************************************************
 * All plugins take a App/Post object, transform it and return true if succesful
 **********************************************************************************
 *
 * About This Plugin
 * -------------------
 * Makes posts that contain certain words in their title as read
 *
 * Modified Properties:
 * --------------------
 * [List the properties that will be modified by this plugin]
 * [Example: Post->content]
 */

class PluginMarkPostAsRead implements PluginInterface
{
    private $post;

    public function __construct(Post $post)
    {
        $this->post = $post;
    }

    public function handle()
    {
        try {
            $phrases_to_blacklist = [
                ['url' => 'slowboring.com', 'string' => 'thread'],
                ['url' => 'macstories.net', 'string' => 'AppStories, Episode'],
                ['url' => 'macstories.net', 'string' => '[Sponsor]'],
                ['url' => 'macstories.net', 'string' => 'MacStories Unwind'],
                ['url' => 'caseyliss.com', 'string' => 'Appearance:'],
            ];

            foreach ($phrases_to_blacklist as $phrase) {
                // if a match is found, mark a post as read.
                if (str_contains($this->post->source->url, $phrase['url']) && str_contains($this->post->title, $phrase['string'])) {
                    $this->post->read = 1;
                }
            }

            $this->post->save();

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
