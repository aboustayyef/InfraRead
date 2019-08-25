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

            $saving_string = 'https://www.instapaper.com/api/add?'.
            'username='. urlencode(env('INSTAPAPER_USERNAME')).
            '&password='. urlencode(env('INSTAPAPER_PASSWORD')).
            '&url='. urlencode($request->get('url'));

            $client = new Client();
            $res = $client->request('GET', $saving_string, [
                'headers' => [
                    'Accept' => 'application/json', 
                    'Content-type' => 'application/json'
                ]
            ]);

            return response($res->getBody());
        }

        return response('This request needs a url', 403);
    }
}