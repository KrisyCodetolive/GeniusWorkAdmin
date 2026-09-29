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
        // Webhooks externes (passerelles de paiement)
        'webhooks/*',
    ];

    /**
     * Vérifier si la requête est exemptée de la protection CSRF.
     */
    public function handle($request, \Closure $next)
    {
        // Exempter les routes kiosk (scan de QR code)
        if (preg_match('#^/kiosk/[a-zA-Z0-9_-]+/scan$#', $request->getPathInfo())) {
            return $next($request);
        }

        return parent::handle($request, $next);
    }
}
