<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],
    'openai' => [
        'key' => env('OPENAI_KEY'),
    ],
    'mercury' => [
        'api_key' => env('MERCURY_API_KEY'),
    ],
    'pocket' => [
        'consumer_key' => env('POCKET_CONSUMER_KEY'),
        'access_token' => env('POCKET_ACCESS_TOKEN'),
    ],
    'instapaper' => [
        'username' => env('INSTAPAPER_USERNAME'),
        'password' => env('INSTAPAPER_PASSWORD'),
    ],
    'omnivore' => [
        'api_key' => env('OMNIVORE_API_KEY'),
    ],
    'narrator' => [
        'api_token' => env('NARRATOR_API_TOKEN'),
    ],

];
