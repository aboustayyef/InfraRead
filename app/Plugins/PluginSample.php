<?php

namespace App\Plugins;

use App\Models\Post;

/**********************************************************************************
 * All plugins take a App/Post object, transform it and return true if successful
 **********************************************************************************
 *
 * About This Plugin
 * -------------------
 * Sample Plugin - Template for creating new plugins
 *
 * Modified Properties:
 * --------------------
 * [List the properties that will be modified by this plugin]
 * [Example: Post->content]
 */

class PluginSample implements PluginInterface
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
            // All plugin's logic should be inside the try() function
            /**************************

            Plugin logic goes here
            the logic is supposed to modify the $this->post object

            You can use $this->options to access plugin configuration:
            $someOption = $this->options['some_option'] ?? 'default_value';

            **************************/
            $this->post->save();

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function getMetadata(): array
    {
        return [
            'name' => 'Sample Plugin',
            'description' => 'Template plugin for creating new post processing plugins',
            'version' => '1.0.0',
            'author' => 'InfraRead',
            'modifies' => ['example_field'],
            'options' => [
                'example_option' => 'Description of what this option does'
            ]
        ];
    }
}
