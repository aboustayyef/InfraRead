<?php

namespace App\Plugins;

use App\Models\Post;

/**********************************************************************************
 * All plugins take a App/Post object, transform it and return true if successful
 **********************************************************************************
 *
 * About This Plugin
 * -------------------
 * Makes posts that contain certain words in their title as read
 *
 * Modified Properties:
 * --------------------
 * Post->read - Sets to 1 if title matches blacklisted phrases for specific sources
 */

class PluginMarkPostAsRead implements PluginInterface
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
            // Get blacklist from options or use default
            $phrases_to_blacklist = $this->options['blacklist'] ?? [
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

    public function getMetadata(): array
    {
        return [
            'name' => 'Mark Post As Read',
            'description' => 'Automatically marks posts as read based on title patterns and source URLs',
            'version' => '1.1.0',
            'author' => 'InfraRead',
            'modifies' => ['read'],
            'options' => [
                'blacklist' => 'Array of url/string patterns to match for auto-marking as read'
            ]
        ];
    }
}
