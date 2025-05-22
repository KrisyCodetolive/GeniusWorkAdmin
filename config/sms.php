<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Configuration par défaut
    |--------------------------------------------------------------------------
    |
    | Définit le service SMS par défaut à utiliser pour les notifications système
    | Options disponibles : 'smslab', 'orange'
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
];
