<?php

namespace App;

use Embed\Embed;

class UrlAnalyzer
{
    public $status;
    public $error_messages;
    public $result;
    private $url;

    private $html;

    private $embed;

    public function __construct($url)
    {
        $this->url = $url;
        $this->status = 'ok';
        $this->error_messages = [];
        $this->result = [];

        // Check url is valid
        if (filter_var($url, FILTER_VALIDATE_URL) === false) {
            return $this->abort('URL is not valid. Make Sure it starts with http:// or https://');
        }

        // Check url is accessible
        try {
            $embed = new Embed();
            $this->embed = $embed->get($this->url);
        } catch (\Embed\Exceptions\InvalidUrlException $e) {
            return $this->abort('Cannot Access URL');
        }

        $this->result['title'] = $this->embed->title;
        $this->result['description'] = $this->embed->description;
        $this->result['author'] = $this->embed->authorName;
        $this->result['rss'] = (string) $this->embed->feeds[0];
        $this->result['canonical_url'] = (string) $this->embed->url;
    }

    private function abort($message)
    {
        $this->status = 'error';
        $this->error_messages[] = $message;

        return;
    }
}
