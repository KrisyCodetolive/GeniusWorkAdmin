<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Enregistrement immédiat des données
    |--------------------------------------------------------------------------
    |
    | Cette option détermine si les données du workflow doivent être enregistrées
    | dans la base de données à chaque étape (true) ou seulement à la fin du
    | processus (false).
    |
    */
    'save_data_immediately' => env('WORKFLOW_SAVE_IMMEDIATELY', false),

    /*
    |--------------------------------------------------------------------------
    | Durée de conservation des données en session
    |--------------------------------------------------------------------------
    |
    | Durée (en minutes) pendant laquelle les données du workflow sont conservées
    | en session avant d'être automatiquement supprimées.
    |
    */
    'session_lifetime' => env('WORKFLOW_SESSION_LIFETIME', 60),

    /*
    |--------------------------------------------------------------------------
    | Redirection après inscription
    |--------------------------------------------------------------------------
    |
    | Route vers laquelle l'utilisateur sera redirigé après avoir complété
    | le processus d'inscription.
    |
    */
    'redirect_after_completion' => env('WORKFLOW_REDIRECT_AFTER_COMPLETION', 'dashboard'),
];
