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

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
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

    'weatherapi' => [
        'demo_sesh' => env('DEMO_SESH', false),
        'weatherapi_key' => env('WEATHERAPI_KEY', 'Default Value'),
        'images_dir' => env('IMAGE_DIRECTORY', ''),
        'days_to_fetch' => env('DAYS_TO_FETCH', 7),
        'font_family' => env( 'FONT_FAMILY','fonts/Brush Script.ttf'),
        'font_color' => env('FONT_COLOR', '#ffffff'),
        'font_size' => env('FONT_SIZE', 12),
        'heat_unit' => env('HEAT_UNIT', 'c'),
        'speed_unit' => env('SPEED_UNIT', 'kpm'),
        'precision' => env('PRECISION', '1'),
        'latitude' => env('LATITUDE', '-106.5798192153439'),
        'longtitude' => env('LONGITUDE', '31.846848732688155'),
        'app_locale' => env('APP_LOCALE', 'en'),
    ],

];