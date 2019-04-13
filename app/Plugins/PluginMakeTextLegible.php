<?php

namespace App\Plugins;
use \App\Post;

/**
 * This Plugin Cleans Up the text at Slashdot, which comes without paragraphs.
 */

class PluginMakeTextLegible implements PluginInterface
{
    private $post;
    function __construct(Post $post)
    {
        $this->post = $post;
    }

    function handle(){
        
        $max_length_of_paragraph = 120 ; // number of words allowed in first paragraph.

        // Isolate content from comments and sharing button fluff;
        $parts = explode('<p></p>', $this->post->content);
        $content = $parts[0];

        //-- remove line breaks
        $content = str_replace("\n", "", $content);
        $content = str_replace("\r", "", $content);

        // Divide into short sentences        
        $phrases = $this->breakLongText($content, 130, $max_length_of_paragraph);
        $content = '<p>' . implode($phrases,'</p><p>').'</p>';

        // then re-attach it
        $parts[0] = $content;
        $cleaned_content = implode($parts);
        $this->post->content = $cleaned_content;
        $this->post->save();
    }

    private function breakLongText($text, $length = 200, $maxLength = 250){ 
    // Source: http://www.brainbell.com/tutorials/php/long-to-small-paragraph.html

        //Get Text length
        $textLength = strlen($text);

        //initialize empty array to store split text
        $splitText = array();

        //return without breaking if text is already short
        if (!($textLength > $maxLength)){
        $splitText[] = $text;
        return $splitText;
        }

        //Guess sentence completion
        $needle = '. ';

        /*iterate over $text length 
        as substr_replace deleting it*/  
        while (strlen($text) > $length){

        $end = strpos($text, $needle, $length);

        if ($end === false){

        //Returns FALSE if the needle (in this case ".") was not found.
        $splitText[] = substr($text,0);
        $text = '';
        break;

        }

        $end++;
        $splitText[] = substr($text,0,$end);
        $text = substr_replace($text,'',0,$end);

        }

        if ($text){
        $splitText[] = substr($text,0);
        }

        return $splitText;

    }
}