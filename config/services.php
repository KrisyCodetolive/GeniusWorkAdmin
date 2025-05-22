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

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    // Configuration pour les services de notification
    'sms' => [
        'enabled' => env('SMS_ENABLED', false),
    ],

    // Configuration pour Twilio (SMS)
    'twilio' => [
        'enabled' => env('TWILIO_ENABLED', false),
        'sid' => env('TWILIO_SID'),
        'token' => env('TWILIO_TOKEN'),
        'from' => env('TWILIO_FROM'),
    ],

    // Configuration pour Vonage/Nexmo (SMS)
    'vonage' => [
        'enabled' => env('VONAGE_ENABLED', false),
        'key' => env('VONAGE_KEY'),
        'secret' => env('VONAGE_SECRET'),
        'from' => env('VONAGE_FROM'),
    ],

    // Configuration pour Genius Tools (QR Codes)
    'genius_tools' => [
        'api_key' => env('GENIUS_TOOLS_API_KEY', '211313bf1816d4c5c54f59362c36e660'),
        'base_url' => env('GENIUS_TOOLS_BASE_URL', 'https://linkqr.genius.ci/api'),
    ],

];
