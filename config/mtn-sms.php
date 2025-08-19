<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Configuration MTN SMS
    |--------------------------------------------------------------------------
    |
    | Configuration pour l'API MTN SMS
    |
    */

    // Environnement : 'prod' ou 'sandbox'
    'environment' => env('MTN_SMS_ENVIRONMENT', 'sandbox'),
    
    // Identifiants pour l'environnement de production
    'prod' => [
        'client_id' => env('MTN_SMS_CLIENT_ID'),
        'client_secret' => env('MTN_SMS_CLIENT_SECRET'),
        'api_base_url' => env('MTN_SMS_API_BASE_URL_PROD', 'https://api.mtn.com'),
        'sms_api_version' => 'v3',
        'token_path' => '/v1/oauth/access_token/accesstoken',
        'sms_path' => '/v3/sms/messages/sms/outbound',
    ],
    
    // Identifiants pour l'environnement sandbox
    'sandbox' => [
        'client_id' => env('MTN_SMS_SANDBOX_CLIENT_ID', env('MTN_SMS_CLIENT_ID')),
        'client_secret' => env('MTN_SMS_SANDBOX_CLIENT_SECRET', env('MTN_SMS_CLIENT_SECRET')),
        'api_base_url' => env('MTN_SMS_API_BASE_URL_SANDBOX', 'https://sandbox.api.mtn.com'),
        'sms_api_version' => 'v2',
        'token_path' => '/v1/oauth/access_token/accesstoken',
        'sms_path' => '/v2/messages/sms/outbound',
    ],
    
    // Paramètres communs
    'dev_phone_number' => env('MTN_SMS_DEV_PHONE_NUMBER'),
    'sender_address' => env('MTN_SMS_SENDER_ADDRESS', 'Genius Work'),
    'service_code' => env('MTN_SMS_SERVICE_CODE', '131'),
];
