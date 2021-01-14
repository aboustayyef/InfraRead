<?php

namespace App\Utilities;

use Exception;
use GuzzleHttp\Client;

class ReadLater
{
    private $url;

    public function __construct($url)
    {
        // Check if Instapaper or Pocket are set up
        if (!env('POCKET_ACCESS_TOKEN') && !env('INSTAPAPER_USERNAME') && !env('PREFERRED_READLATER_SERVICE')) {
            throw new Exception('You have to setup either Instapaper or Pocket and choose which one you prefer');
        }
        // Validate URL
        if (filter_var($url, FILTER_VALIDATE_URL)) {
            $this->url = filter_var($url, FILTER_VALIDATE_URL);
        } else {
            throw new Exception('Url is not valid', 1);
        }
    }

    public function save()
    {
        if (env('PREFERRED_READLATER_SERVICE') == 'pocket') {
            $response = json_decode((string) $this->saveToPocket());
        } else {
            $response = json_decode((string) $this->saveToInstapaper());
        }

        if (isset($response->bookmark_id) || $response->status == 1) {
            return true;
        }
        throw new Exception('Couldnt save url', 1);
    }

    public function saveToPocket()
    {
        $client = new Client();
        $res = $client->request('POST', 'https://getpocket.com/v3/add', [
            'form_params' => [
                'url' => urldecode($this->url),
                'consumer_key' => env('POCKET_CONSUMER_KEY'),
                'access_token' => env('POCKET_ACCESS_TOKEN'),
            ],
        ]);

        return $res->getBody();
    }

    public function saveToInstapaper()
    {
        $saving_string = 'https://www.instapaper.com/api/add?'.
        'username='.urlencode(env('INSTAPAPER_USERNAME')).
        '&password='.urlencode(env('INSTAPAPER_PASSWORD')).
        '&url='.$this->url;

        $client = new Client();
        $res = $client->request('GET', $saving_string, [
            'headers' => [
                'Accept' => 'application/json',
                'Content-type' => 'application/json',
            ],
        ]);

        return $res->getBody();
    }
}
