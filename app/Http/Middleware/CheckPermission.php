<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $permission
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next, $permission = null): Response
    {
        // Temporairement, toujours autoriser
        // Vous pourrez implémenter la vérification réelle des permissions plus tard
        return $next($request);
        
        // Implémentation future :
        /*
        if (!$request->user() || !$request->user()->hasPermissionTo($permission)) {
            abort(403, 'Accès non autorisé');
        }
        return $next($request);
        */
    }
}
