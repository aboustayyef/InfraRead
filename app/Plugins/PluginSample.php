<?php

namespace App\Plugins;
use \App\Post;

/**********************************************************************************
 * All plugins take a App/Post object, transform it and return true if succesful
 **********************************************************************************
 *
 * About This Plugin
 * -------------------
 * NameOfPlugin [1 line description of what this plugin does]
 * 
 * Modified Properties:
 * --------------------
 * [List the properties that will be modified by this plugin]
 * [Example: Post->content]
 */

class PluginSample implements PluginInterface
{
    private $post;
    function __construct(Post $post)
    {
        $this->post = $post;
    }

    function handle(){
        try {
            // All plugin's logic should be inside the try() function
            /**************************
   
            Plugin logic goes here
            the logic is supposed to modify the $this->post object

            **************************/
            $this->post->save();
            return true;        
        } catch (\Exception $e) {
            return false;
        }

    }

}