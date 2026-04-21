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
    'remote_restore' => [
        'ssh' => [
            'host' => env('REMOTE_RESTORE_SSH_HOST'),
            'port' => env('REMOTE_RESTORE_SSH_PORT', 22),
            'user' => env('REMOTE_RESTORE_SSH_USER'),
            'key_path' => env('REMOTE_RESTORE_SSH_KEY_PATH'),
        ],
        'database' => [
            'host' => env('REMOTE_RESTORE_DB_HOST', '127.0.0.1'),
            'port' => env('REMOTE_RESTORE_DB_PORT', 3306),
            'database' => env('REMOTE_RESTORE_DB_DATABASE'),
            'user' => env('REMOTE_RESTORE_DB_USER'),
            'password' => env('REMOTE_RESTORE_DB_PASSWORD'),
        ],
        'remote_tmp_dir' => env('REMOTE_RESTORE_TMP_DIR', '/tmp'),
        'local_tmp_dir' => env('REMOTE_RESTORE_LOCAL_TMP_DIR', 'storage/app/restores'),
        'use_gzip' => env('REMOTE_RESTORE_USE_GZIP', true),
    ],
];
