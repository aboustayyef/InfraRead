<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use GuzzleHttp\Client;

class ReadlaterController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if ($request->has('url')) {

            $url = urlencode($request->get('url'));
            $readlaterservice = env('PREFERRED_READLATER_SERVICE'); 
            if ( $readlaterservice == 'pocket') {
                return $this->saveToPocket($url);
            }

            if ($readlaterservice == 'instapaper') {
                return $this->saveToInstapaper($url);
            }

            return "You need to Choose a prefered Read Later Service at .env. 
            It has to be either 'instapaper' or 'pocket'";

        }

        return response('This request needs a url', 403);
    }

    public function saveToPocket($url){
        if (strlen(env('POCKET_ACCESS_TOKEN')) > 1){
            $client = new Client(); 
            $res= $client->request('POST', 'https://getpocket.com/v3/add', [
                'form_params' => [
                    'url' => urldecode($url),
                    'consumer_key' => env('POCKET_CONSUMER_KEY'),
                    'access_token' => env('POCKET_ACCESS_TOKEN'),
                ]
            ]);
            return response($res->getBody());
        } else {
            return redirect('/app/setuppocket');
        }
    }

    public function saveToInstapaper($url){
        $saving_string = 'https://www.instapaper.com/api/add?'.
        'username='. urlencode(env('INSTAPAPER_USERNAME')).
        '&password='. urlencode(env('INSTAPAPER_PASSWORD')).
        '&url='. $url;

        $client = new Client();
        $res = $client->request('GET', $saving_string, [
            'headers' => [
                'Accept' => 'application/json', 
                'Content-type' => 'application/json'
            ]
        ]);

        return response($res->getBody());
    }
}