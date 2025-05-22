<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class ImpersonateMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Vérifier si l'utilisateur est en train d'impersonate
        if (Session::has('impersonate_origin_id')) {
            // Partager cette information avec toutes les vues
            view()->share('impersonating', true);
            view()->share('impersonateOriginId', Session::get('impersonate_origin_id'));
            view()->share('impersonateOriginRole', Session::get('impersonate_origin_role'));
            
            // Ajouter une variable pour indiquer que l'utilisateur actuel est impersonaté
            if (Auth::check()) {
                Auth::user()->is_impersonated = true;
            }
        }

        return $next($request);
    }
}
