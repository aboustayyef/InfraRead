<?php

namespace App\Plugins;

use App\Models\Post;

/**********************************************************************************
 * All plugins take a App/Post object, transform it and return true if succesful
 **********************************************************************************
 *
 * About This Plugin
 * -------------------
 * MakeTextLegible Cleans Up the text at feeds that have content text with no paragraphs
 *
 * Modified Properties:
 * --------------------
 * Post->Content
 */

class PluginMakeTextLegible implements PluginInterface
{
    private $post;

    public function __construct(Post $post)
    {
        $this->post = $post;
    }

    public function handle()
    {
        // All plugin's logic should be inside the try() function
        try {
            $max_length_of_paragraph = 120; // number of words allowed in first paragraph.

            // Isolate content from comments and sharing button fluff;
            $parts = explode('<p></p>', $this->post->content);
            $content = $parts[0];

            //-- remove line breaks
            $content = str_replace("\n", '', $content);
            $content = str_replace("\r", '', $content);

            // Divide into short sentences
            // $phrases = $this->breakLongText($content, 130, $max_length_of_paragraph);
            $cleaned_content = $this->breakLongText($content, 300);

            $this->post->content = $cleaned_content;
            $this->post->save();

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    function breakLongText($text, $minLettersCount = 350)
    {
        // Sometimes sentences include abbrevations containing dots. We shouldn't use them to divide paragraphs
        $ignoredAbbreviations = ['U.S.'];
        $paragraphs = [];

        // Split the text into sentences using delimiters ".", "!", and "?"
        $sentences = preg_split('/(?<=[.!?])\s+/', $text);

        // Initialize a variable to hold the current paragraph
        $currentParagraph = '';

        // Iterate through the sentences
        foreach ($sentences as $sentence) {
            // Trim any leading or trailing whitespace from the sentence
            $sentence = trim($sentence);

            // Check if the sentence is an abbreviation to ignore
            $isAbbreviation = false;
            foreach ($ignoredAbbreviations as $abbreviation) {
                if (stripos($sentence, $abbreviation) !== false) {
                    $isAbbreviation = true;
                    break;
                }
            }

            // If the sentence is not empty and not an abbreviation, add it to the current paragraph
            if (!empty($sentence)) {
                $currentParagraph .= $sentence . ' ';
            }

            // If the sentence is empty (end of a paragraph) or meets the minimum letters count, add the current paragraph to the array
            if (empty($sentence) || mb_strlen($currentParagraph) >= $minLettersCount || $sentence === end($sentences)) {

                // If the paragraph doesn't end with an abbreviation, Add it to the array
                if (!$isAbbreviation) {

                    // Trim any trailing whitespace from the current paragraph
                    $currentParagraph = rtrim($currentParagraph);
                    $paragraphs[] = '<p>' . $currentParagraph . '</p>';

                    // Reset the current paragraph
                    $currentParagraph = '';
                }
            }
        }

        // Join the paragraphs with line breaks and return the HTML code
        return implode("\n", $paragraphs);
    }

    private function breakLongText_old($text, $length = 200, $maxLength = 250)
    {
        // Source: http://www.brainbell.com/tutorials/php/long-to-small-paragraph.html

        //Get Text length
        $textLength = strlen($text);

        //initialize empty array to store split text
        $splitText = [];

        //return without breaking if text is already short
        if (!($textLength > $maxLength)) {
            $splitText[] = $text;

            return $splitText;
        }

        //Guess sentence completion
        $needle = '. ';

        /*iterate over $text length
        as substr_replace deleting it*/
        while (strlen($text) > $length) {
            $end = strpos($text, $needle, $length);

            if ($end === false) {
                //Returns FALSE if the needle (in this case ".") was not found.
                $splitText[] = substr($text, 0);
                $text = '';
                break;
            }

            ++$end;
            $splitText[] = substr($text, 0, $end);
            $text = substr_replace($text, '', 0, $end);
        }

        if ($text) {
            $splitText[] = substr($text, 0);
        }

        return $splitText;
    }
}
