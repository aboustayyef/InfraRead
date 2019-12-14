<?php

namespace App\Http\Controllers;
use GuzzleHttp\Client;
use Illuminate\Http\Request;

class PocketSetupController extends Controller
{
    //
    public function index()
    {
        // step 1: Get a Pocket request token

        $client = new Client(); 
        $res= $client->request('POST', 'https://getpocket.com/v3/oauth/request', [
            'form_params' => [
                'consumer_key' => env('POCKET_CONSUMER_KEY'),
                'redirect_uri' => env('APP_URL'). '/app/pocketsetup',
            ]
        ]);

        $returned_string = (string) $res->getBody();
        if (str_contains($returned_string, 'code=')) {
            $code = str_replace('code=','',$returned_string);
            session(['code' => $code]);
            return redirect('https://getpocket.com/auth/authorize?request_token='.$code.'&redirect_uri='. env('APP_URL'). '/app/setuppocket/authorise');
        }


    }

    public function authorise()
    {
        echo "authorising...<br>";
        $code = request()->session()->get('code');
        $client = new Client(); 
        $res= $client->request('POST', 'https://getpocket.com/v3/oauth/authorize', [
            'form_params' => [
                'consumer_key' => env('POCKET_CONSUMER_KEY'),
                'code' => $code,
            ]
        ]);
        $pocket_access_token = $this->get_access_token_from_string($res->getBody());
        session(['pocket_access_token'=> $pocket_access_token]);
        return 'Your Pocket Access Token is: <strong>' . $pocket_access_token . '</strong> <br>Make Sure you add it to your .env file then reload app';
    }

    private function get_access_token_from_string($s){
        // extract token from a string like 'access_token=TOKEN&username=USERNAME'
        $a = explode('=', $s);
        $t = str_replace('&username','',$a[1]);
        return $t; 
    }
}
