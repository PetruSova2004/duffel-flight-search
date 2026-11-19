<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Resend, Postmark, AWS, and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'duffel' => [
        'token' => env('DUFFEL_TOKEN'),
        'base_url' => env('DUFFEL_BASE_URL', 'https://api.duffel.com'),
        'version' => 'v2',
        // Must stay below PHP's max_execution_time (30 s by default)
        'timeout' => (int) env('DUFFEL_TIMEOUT', 28),
        'connect_timeout' => (int) env('DUFFEL_CONNECT_TIMEOUT', 10),
        'supplier_timeout' => (int) env('DUFFEL_SUPPLIER_TIMEOUT', 20000),
        // Two days, so a failed daily refresh still leaves the old list in place
        'places_ttl' => (int) env('DUFFEL_PLACES_TTL', 172800),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

];
