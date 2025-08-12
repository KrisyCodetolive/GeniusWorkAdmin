<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Configuration par défaut
    |--------------------------------------------------------------------------
    |
    | Définit le service SMS par défaut à utiliser pour les notifications système
    | Options disponibles : 'smslab', 'orange', 'mtn'
    |
    */
    'default_provider' => env('SMS_DEFAULT_PROVIDER', 'orange'),

    /*
    |--------------------------------------------------------------------------
    | Configuration Orange SMS
    |--------------------------------------------------------------------------
    */
    'orange' => [
        'client_id' => env('ORANGE_SMS_CLIENT_ID'),
        'client_secret' => env('ORANGE_SMS_CLIENT_SECRET'),
        'dev_phone_number' => env('ORANGE_SMS_DEV_PHONE_NUMBER'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Configuration MTN SMS
    |--------------------------------------------------------------------------
    */
    'mtn' => [
        'client_id' => env('MTN_SMS_CLIENT_ID'),
        'client_secret' => env('MTN_SMS_CLIENT_SECRET'),
        'dev_phone_number' => env('MTN_SMS_DEV_PHONE_NUMBER'),
        'sender_address' => env('MTN_SMS_SENDER_ADDRESS'),
        'api_base_url' => env('MTN_SMS_API_BASE_URL', 'https://api.mtn.com/v2'),
    ],
];
