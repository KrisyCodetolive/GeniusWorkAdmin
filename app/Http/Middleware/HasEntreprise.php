<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class HasEntreprise
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
            return redirect()->route('login')->with('error', 'Veuillez vous connecter.');
        }
        
        $user = Auth::user();
        
        // Ignorer si l'utilisateur est superadmin ou support
        if ($user->isSuperAdmin() || $user->isSupport()) {
            return $next($request);
        }

        // Vérifier si l'utilisateur a une entreprise associée
        // Utiliser la méthode relationLoaded pour vérifier si la relation est chargée
        // ou accéder à l'ID de l'entreprise directement
        if (!$user->entreprise_id) {
            return redirect()->route('home')->with('error', 'Aucune entreprise associée à votre compte.');
        }

        return $next($request);
    }
}
