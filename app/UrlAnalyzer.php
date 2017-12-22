<?php 
namespace App;
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
    public $status, $errorMessages, $result; 
    private $html, $guzzleClient, $guzzleResponse, $crawler;

    function __construct($url)
    {
        $this->url = $url;
        $this->status = 'ok';
        $this->errorMessages = [];
        $this->result = [];

        // Check url is valid
        if (filter_var($url, FILTER_VALIDATE_URL) === FALSE) {
            return $this->abort('URL is not valid');
        }


        // Check url is accessible
        $guzzleClient = new Client();
        try {       
            $guzzleResponse = $guzzleClient->get($this->url)->send();
        } catch (Exception $e) {
            return $this->abort('Could not get content of URL. Please try again');
        }

        if ($guzzleResponse->getStatusCode() !== 200) {
            return $this->abort('Could not get content of URL. Please try again');
        }

        // get title and subtitle
        $this->html = (string) $guzzleResponse->getBody();
        $this->crawler = new Crawler($this->html);

        // if any of the below cannot be found, we get status = 'warning' and show the missing items;
        
        $this->getTitleAndDescription();
        $this->getRssFeed();

        // $this->getDescription();
        // $this->getRss();


    }

    private function urlIsValid($url){
        return true;
        // return preg_match('/^(https?:\/\/)?([\da-z\.-]+\.[a-z\.]{2,6}|[\d\.]+)([\/:?=&#]{1}[\da-z\.-]+)*[\/\?]?$/i', $url);
    }

    private function abort($message){
        $this->status = 'error';
        $this->errorMessages[] = $message;
        return;
    }

    private function getTitleAndDescription(){
        $rawTitle = $this->crawler->filter('title')->first()->text();
        
        // some titles come with description, split them;
        $partsOfTitle = preg_split('#\s*\||–\s*#',$rawTitle);

        $this->result['title'] = trim($partsOfTitle[0]);
        if (isset($partsOfTitle[1])) {
            $this->result['description'] = trim($partsOfTitle[1]);
        }else{
            $this->status = 'warning';
            $this->errorMessages[] = 'Could not extract description';
        }
    }

    private function getRssFeed(){
        try {
            $this->result['feed'] = $this->crawler->filter('link[type="application/rss+xml"]')->first()->attr('href');
        } catch (Exception $e) {
            $this->status = 'warning';
            $this->errorMessages[] = 'Could not automatically extract RSS Feed';
        }
    }
}
?>