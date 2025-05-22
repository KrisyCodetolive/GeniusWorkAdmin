<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class EntrepriseAccessMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Vérifier si l'utilisateur est authentifié
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Vous devez être connecté pour accéder à cette page.');
        }

        // Les SuperAdmin et Support ont accès à toutes les données, indépendamment de l'entreprise
        if (Auth::user()->isSuperAdmin() || Auth::user()->isSupport()) {
            return $next($request);
        }

        // Vérifier si l'utilisateur a une entreprise associée
        if (!Auth::user()->entreprise_id) {
            Log::warning('Tentative d\'accès sans entreprise associée', [
                'user_id' => Auth::id(),
                'ip' => $request->ip(),
                'url' => $request->fullUrl()
            ]);
            return redirect()->route('home')->with('error', 'Aucune entreprise associée à votre compte.');
        }

        // Vérifier si l'entreprise est active
        if (Auth::user()->entreprise && Auth::user()->entreprise->statut !== 'actif') {
            Log::warning('Tentative d\'accès avec une entreprise inactive', [
                'user_id' => Auth::id(),
                'entreprise_id' => Auth::user()->entreprise_id,
                'ip' => $request->ip(),
                'url' => $request->fullUrl()
            ]);
            return redirect()->route('home')->with('error', 'Votre entreprise n\'est pas active. Veuillez contacter l\'administrateur.');
        }

        // Vérifier si l'utilisateur tente d'accéder à des données d'une autre entreprise via un paramètre d'URL
        $entrepriseIdFromRequest = $request->route('entreprise');
        if ($entrepriseIdFromRequest && $entrepriseIdFromRequest !== Auth::user()->entreprise_id) {
            Log::warning('Tentative d\'accès aux données d\'une autre entreprise', [
                'user_id' => Auth::id(),
                'user_entreprise_id' => Auth::user()->entreprise_id,
                'requested_entreprise_id' => $entrepriseIdFromRequest,
                'ip' => $request->ip(),
                'url' => $request->fullUrl()
            ]);
            return redirect()->route('home')->with('error', 'Vous n\'avez pas les permissions nécessaires pour accéder à ces données.');
        }

        return $next($request);
    }
}
