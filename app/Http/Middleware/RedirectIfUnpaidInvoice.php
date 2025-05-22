<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Facturation;
use Illuminate\Support\Facades\Log;

class RedirectIfUnpaidInvoice
{
    /**
     * Redirige l'utilisateur vers la page de paiement s'il a des factures impayées
     * sauf s'il est SuperAdmin ou Support
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();
        
        // Si l'utilisateur n'est pas connecté ou est SuperAdmin/Support, on le laisse passer
        if (!$user || $user->isSuperAdmin() || $user->isSupport()) {
            return $next($request);
        }
        
        // Si l'utilisateur est déjà sur la page de paiement, on le laisse passer
        if ($request->is('paiement*') || $request->is('factures*')) {
            return $next($request);
        }
        
        // Vérifier si l'utilisateur a des factures impayées
        $entrepriseId = $user->entreprise_id;
        $facturesImpayees = Facturation::where('entreprise_id', $entrepriseId)
            ->where('statut_paiement', 'en_attente')
            ->where('date_facturation', '<', now())
            ->orderBy('date_facturation', 'asc')
            ->get();

        
            
        if ($facturesImpayees->count() > 0) {
            Log::info("Factures impayées pour l'entreprise {$entrepriseId}", [
                'facturesImpayees' => $facturesImpayees->count()
            ]);
            // Récupérer la première facture impayée (la plus urgente)
            $facture = $facturesImpayees->first();
            $facturationId = $facture->id;
            
            // Calculer le montant total des factures impayées
            $montantTotalImpaye = $facturesImpayees->sum('montant_ttc');
            
            // Rediriger vers la page de paiement des factures avec les données
            return redirect()->route('facturations.paiement')
                ->with([
                    'warning' => 'Vous avez des factures en attente de paiement. Veuillez les régler avant de continuer.',
                    'facturesImpayees' => $facturesImpayees,
                    'facture' => $facture,
                    'facturationId' => $facturationId,
                    'montantTotalImpaye' => $montantTotalImpaye
                ]);
        }
        
        return $next($request);
    }
}
