<?php

// Autorise toutes les origines, méthodes et en-têtes.
// Avec l'origine « * », le navigateur n'envoie pas les cookies de session sur les requêtes
// cross-origin (supports_credentials doit rester false) : à restreindre avant la production.
return [
    'paths' => ['*'],
    'allowed_methods' => ['*'],
    'allowed_origins' => ['*'],
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => false,
];
