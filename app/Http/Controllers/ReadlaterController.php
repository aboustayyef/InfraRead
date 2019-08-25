<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

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
            try {
                $save_status = file_get_contents($saving_string);
            } catch (\Exception $e) {
                $save_status = 'server error: '. $saving_string;  
            }

            return response($save_status);
        }
        return response('This request needs a url', 403);
    }
}