<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Service d'Email Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration spécifique pour les services d'emails de l'application.
    |
    */

    'smtp' => [
        'host' => env('MAIL_HOST', 'mail.genius.ci'),
        'port' => env('MAIL_PORT', 465),
        'username' => env('MAIL_USERNAME', 'work@genius.ci'),
        'password' => env('MAIL_PASSWORD', 'work@genius.ci'),
        'encryption' => env('MAIL_ENCRYPTION', 'tls'),
        'from_address' => env('MAIL_FROM_ADDRESS', 'work@genius.ci'),
        'from_name' => env('MAIL_FROM_NAME', 'Genius Work'),
        'reply_to_address' => env('MAIL_REPLY_TO_ADDRESS', 'support@genius.ci'),
        'reply_to_name' => env('MAIL_REPLY_TO_NAME', 'Support Genius Work'),
        'default_bcc' => [
            'it@genius.ci',
            'work@genius.ci'
        ],
        // Paramètres anti-spam
        'dkim_domain' => env('MAIL_DKIM_DOMAIN', 'genius.ci'),
        'dkim_selector' => env('MAIL_DKIM_SELECTOR', 'default'),
    ],

    'logging' => [
        'channel' => 'emails',
        'retention_days' => 30,
    ],
];
