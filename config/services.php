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

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'google' => [
        'service_account_json' => env('GOOGLE_APPLICATION_CREDENTIALS') 
            ? (str_starts_with(env('GOOGLE_APPLICATION_CREDENTIALS'), '/') || preg_match('/^[A-Za-z]:/', env('GOOGLE_APPLICATION_CREDENTIALS')) 
                ? env('GOOGLE_APPLICATION_CREDENTIALS') 
                : base_path(env('GOOGLE_APPLICATION_CREDENTIALS')))
            : storage_path('app/google/service-account.json'),
        'oauth_token_path' => storage_path('app/google/oauth-token.json'),
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect_uri' => env('GOOGLE_REDIRECT_URI', 'http://localhost:8000/admin/google/callback'),
    ],

];
