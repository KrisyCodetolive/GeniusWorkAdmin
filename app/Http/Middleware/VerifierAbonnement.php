<?php

namespace App\Http\Middleware;

use App\Services\AbonnementService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VerifierAbonnement
{
    protected $abonnementService;
    
    public function __construct(AbonnementService $abonnementService)
    {
        $this->abonnementService = $abonnementService;
    }
    
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $fonctionnalite
     * @return mixed
     */
    public function handle(Request $request, Closure $next, $fonctionnalite = null)
    {
        $user = Auth::user();
        
        // Ne pas vérifier pour les administrateurs
        if ($user->hasRole('admin')) {
            return $next($request);
        }
        
        // Vérifier si l'utilisateur est associé à une entreprise
        if (!$user->entreprise_id) {
            return redirect()->route('home')->with('error', 'Vous n\'êtes pas associé à une entreprise.');
        }
        
        $entreprise = $user->entreprise;
        
        // Vérifier si l'entreprise a un abonnement actif
        if (!$this->abonnementService->hasAbonnementActif($entreprise)) {
            return redirect()->route('abonnements.index', $entreprise)
                ->with('error', 'Votre entreprise n\'a pas d\'abonnement actif. Veuillez souscrire à un abonnement pour accéder à cette fonctionnalité.');
        }
        
        // Si une fonctionnalité spécifique est requise, vérifier si l'abonnement y donne accès
        if ($fonctionnalite && !$this->abonnementService->hasAccesToFeature($entreprise, $fonctionnalite)) {
            return redirect()->route('abonnements.index', $entreprise)
                ->with('error', 'Votre abonnement actuel ne vous donne pas accès à cette fonctionnalité. Veuillez mettre à niveau votre abonnement.');
        }
        
        return $next($request);
    }
}
