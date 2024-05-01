<?php

namespace App\Utilities;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Str;

class ReadLater
{
    private $url;

    public function __construct($url)
    {
        // Check if Instapaper or Pocket are set up
        if (
            !env('OMNIVORE_API_KEY') &&
            !env('POCKET_ACCESS_TOKEN') &&
            !env('INSTAPAPER_USERNAME') &&
            !env('PREFERRED_READLATER_SERVICE')
        ) {
            throw new Exception('You have to setup either Omnivore, Instapaper or Pocket and choose which one you prefer');
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
            if (isset($response->bookmark_id)) { return true;}
        } elseif (env('PREFERRED_READLATER_SERVICE') == 'omnivore') {
            return $this->saveToOmnivore();
        } else {
            $response = json_decode((string) $this->saveToInstapaper());
            if ($response->status == 1) { return true; }
        }
        \Log::info('$response->saveUrl: ' . $response->saveUrl);
        throw new Exception('Couldnt save url', 1);
    }

    public function saveToOmnivore()
    {

        $client = new Client();

        // Replace '<your api key>' with your actual API key.
        $apiKey = env('OMNIVORE_API_KEY');
        $headers = [
            'Content-Type' => 'application/json',
            'Authorization' => $apiKey
        ];

        $body = json_encode([
            'query' => 'mutation SaveUrl($input: SaveUrlInput!) { saveUrl(input: $input) { ... on SaveSuccess { url clientRequestId } ... on SaveError { errorCodes message } } }',
            'variables' => [
                'input' => [
                    'clientRequestId' => Str::uuid()->toString(),
                    'source' => 'api',
                    'url' => urldecode($this->url)
                ]
            ]
        ]);

        try {
            $response = $client->request('POST', 'https://api-prod.omnivore.app/api/graphql', [
                'headers' => $headers,
                'body' => $body
            ]);
            $json = $response->getBody()->getContents();
            $data = json_decode($json, true);
            if (is_array($data) && array_key_exists('data', $data)) {
                return true; // Save was succesful
            } else {
                return false; // was was not succesful
            }
        } catch (GuzzleException $e) {
            \Log::error($e->getMessage());
            return false;
        }
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
        $saving_string = 'https://www.instapaper.com/api/add?' .
            'username=' . urlencode(env('INSTAPAPER_USERNAME')) .
            '&password=' . urlencode(env('INSTAPAPER_PASSWORD')) .
            '&url=' . $this->url;

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
