<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
    	# Check user status

    	// Scenario one: No user exists
    	if (\App\User::count() == 0) {
    		return response("Hello. It seems you haven't set up your user account yet. <br>Kindly make sure you add your information in the .env file then run <code>php artisan db:seed</code>");
    	}

    	// Scenario two: user exists, but not RSS feeds set up
        // must be logged in, hence redirect
    	if (\App\Source::count() == 0) {
    		return redirect('/setup');
    	}

        // If everything is set, go to the app
        // must be logged in, hence redirect
        return redirect('/app');

    }
}
