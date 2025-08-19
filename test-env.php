<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

// Afficher l'environnement MTN
echo "Environnement MTN: " . env('MTN_SMS_ENVIRONMENT', 'non défini') . "\n";

// Afficher les URLs de base
echo "URL de base PROD: " . env('MTN_SMS_API_BASE_URL_PROD', 'non définie') . "\n";
echo "URL de base SANDBOX: " . env('MTN_SMS_API_BASE_URL_SANDBOX', 'non définie') . "\n";

// Tester la logique de sélection d'URL
$environment = env('MTN_SMS_ENVIRONMENT', 'prod');
$baseUrlKey = 'MTN_SMS_API_BASE_URL_' . strtoupper($environment);
$baseUrl = env($baseUrlKey, $environment === 'prod' ? 'https://api.mtn.com' : 'https://sandbox.api.mtn.com');

echo "Environnement sélectionné: {$environment}\n";
echo "Clé d'URL: {$baseUrlKey}\n";
echo "URL de base sélectionnée: {$baseUrl}\n";
