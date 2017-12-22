<?php

namespace App\Http\Controllers;

use App\UrlAnalyzer;
use Illuminate\Http\Request;

class UrlAnalysisController extends Controller
{
    public function index(Request $request)
    {
    	return response()->json(new UrlAnalyzer($request->get('url')));
    }
}
