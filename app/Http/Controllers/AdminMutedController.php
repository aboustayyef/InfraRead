<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdminMutedController extends Controller
{
    public function index()
    {
        return view('admin.muted');
    }
}
