<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Seuil de requête lente (en millisecondes)
    |--------------------------------------------------------------------------
    |
    | Ce seuil détermine à partir de combien de millisecondes une requête
    | est considérée comme lente et doit être signalée.
    |
    */
    'slow_query_threshold' => env('SLOW_QUERY_THRESHOLD', 1000),

    /*
    |--------------------------------------------------------------------------
    | Journalisation activée
    |--------------------------------------------------------------------------
    |
    | Détermine si la journalisation des requêtes est activée.
    |
    */
    'enabled' => env('QUERY_LOGGING_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Canaux de journalisation
    |--------------------------------------------------------------------------
    |
    | Liste des canaux où les logs seront envoyés.
    |
    */
    'channels' => [
        'queries' => [
            'driver' => 'daily',
            'path' => storage_path('logs/queries.log'),
            'level' => 'debug',
            'days' => 14,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Paramètres à masquer
    |--------------------------------------------------------------------------
    |
    | Liste des paramètres sensibles à masquer dans les logs.
    |
    */
    'hidden_parameters' => [
        'password',
        'password_confirmation',
        'token',
        'secret',
        'api_key'
    ],
];
