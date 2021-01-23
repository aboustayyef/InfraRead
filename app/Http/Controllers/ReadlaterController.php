<?php

namespace App\Http\Controllers;

use App\Utilities\ReadLater;
use Illuminate\Http\Request;

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
            $url = $request->get('url');
            $readlater = new ReadLater($url);
            $response = collect(['status' => 'ok', 'response' => $readlater->save()]);

            return $response;
        }

        return response('This request needs a url', 403);
    }
}
