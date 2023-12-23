<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdminMutedController extends Controller
{
    private $filePath = 'muted_phrases.json';
    public function index()
    {
        $jsonString = Storage::disk('local')->get($this->filePath);
        $mutedPhrases = json_decode($jsonString, true); // Converts to an array
        return view('admin.muted')->with(['mutedPhrases'=>$mutedPhrases]);
    }
}
