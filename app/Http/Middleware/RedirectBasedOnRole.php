<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RedirectBasedOnRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle(Request $request, Closure $next, $guard = null)
    {
        if (Auth::guard($guard)->check()) {
            $user = Auth::user();
            
            // Rediriger en fonction du rôle
            if ($user->isAdmin() || $user->isManager()) {
                return redirect()->route('dashboard.admin');
            } elseif ($user->isEntreprise()) {
                return redirect()->route('dashboard.entreprise');
            } else {
                return redirect()->route('dashboard.employe');
            }
        }

        return $next($request);
    }
}
