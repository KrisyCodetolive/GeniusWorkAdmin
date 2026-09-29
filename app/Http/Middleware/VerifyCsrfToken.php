<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * Routes exemptées de la vérification CSRF.
     * Utilisé pour les routes publiques sans session utilisateur.
     *
     * @var array<int, string>
     */
    protected $except = [
        // Routes du kiosk (pointage sans authentification)
        'kiosk/*/scan',

        // Webhooks externes (passerelles de paiement)
        'webhooks/*',
    ];
}
