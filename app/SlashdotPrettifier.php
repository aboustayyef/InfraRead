<?php 
namespace App;

class SlashdotPrettifier
{

    private $original_content;

    function __construct($original_content)
    {
       $this->original_content = $original_content; 
    }

    public function get(){
        $max_length_of_paragraph = 120 ; // number of words allowed in first paragraph.

        // Isolate content from comments and sharing button fluff;
        $parts = explode('<p></p>', $this->original_content);
        $content = $parts[0];

        // clean up content

        //-- turn new lines into breaks
        $content_with_line_breaks = nl2br($content);
        //-- break up first paragraph.
        //------get first paragraph
        $first_paragraph = explode("<br />", $content_with_line_breaks)[0];
        $words_in_first_paragraph = sizeof(explode(" ", $first_paragraph));

        if ($words_in_first_paragraph > $max_length_of_paragraph) {
            //get all phrases (separated by full stops)
            $phrases = $this->breakLongText($first_paragraph, 130, $max_length_of_paragraph);
            $first_paragraph = '<p>' . implode($phrases,'</p><p>').'</p>';
        }

        // then re-attach it
        $parts[0] = $first_paragraph;

        return implode($parts);
    }

    // Source: http://www.brainbell.com/tutorials/php/long-to-small-paragraph.html
    private function breakLongText($text, $length = 200, $maxLength = 250){
         //Text length
         $textLength = strlen($text);

         //initialize empty array to store split text
         $splitText = array();

         //return without breaking if text is already short
         if (!($textLength > $maxLength)){
          $splitText[] = $text;
          return $splitText;
         }

         //Guess sentence completion
         $needle = '.';

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
?>