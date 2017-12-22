<?php 
namespace App;
use Embed\Embed;
use Guzzle\Http\Client;
use Illuminate\Support\MessageBag;
use Symfony\Component\DomCrawler\Crawler ;
use \Exception;
use \Illuminate\Support\Collection;

/**
*           
*/
class UrlAnalyzer
{
    public $status, $error_messages, $result; 
    private $url, $html, $embed;

    function __construct($url)
    {
        $this->url = $url;
        $this->status = 'ok';
        $this->error_messages = [];
        $this->result = [];

        // Check url is valid
        if (filter_var($url, FILTER_VALIDATE_URL) === FALSE) {
            return $this->abort('URL is not valid');
        }


        // Check url is accessible
        try {       
            $this->embed = Embed::create($this->url);
        } catch (\Embed\Exceptions\InvalidUrlException $e) {
            return $this->abort('Cannot Access URL');
        }
        $this->result['title'] = $this->embed->title;
        $this->result['description'] = $this->embed->description;
        $this->result['author'] = $this->embed->authorName;
        $this->result['rss'] = $this->embed->feeds[0];
        $this->result['canonical_url'] = $this->embed->url;
    }

    private function abort($message)
    {
        $this->status = 'error';
        $this->error_messages[] = $message;
        return;
    }
}
?>