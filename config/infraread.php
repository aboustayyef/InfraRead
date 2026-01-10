<?php

return [
    'api_token' => env('INFRAREAD_API_TOKEN'),
    'preferred_readlater_service' => env('PREFERRED_READLATER_SERVICE', 'none'),
    'opml' => env('OPML', 'default'),
    'opml_export_local_rss_urls' => env('OPML_EXPORT_LOCAL_RSS_URLS', false),
    'admin' => [
        'name' => env('ADMIN_NAME'),
        'email' => env('ADMIN_EMAIL'),
        'password' => env('ADMIN_PASSWORD'),
    ],
];
