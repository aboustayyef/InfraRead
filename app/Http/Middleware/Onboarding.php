<?php

namespace App\Http\Middleware;

use Closure;

class Onboarding
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // If No user exists yet
        if (\App\User::count() == 0) {
            return response("Hello. It seems you haven't set up your user account yet. <br>Kindly make sure you add your information in the .env file then run <code>php artisan db:seed</code>");
        }

        // If a user exists, but not RSS feeds is set up
        if (\App\Source::count() == 0) {
            return redirect('/setup');
        }
        return $next($request);
    }
}
