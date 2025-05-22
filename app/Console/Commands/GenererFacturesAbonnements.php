<?php

namespace App\Console\Commands;

use App\Models\Abonnement;
use App\Models\Facturation;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class GenererFacturesAbonnements extends Command
{
    protected $signature = 'factures:generer {--expired : Générer des factures pour les abonnements déjà expirés}';
    protected $description = 'Générer les factures pour les abonnements à renouvellement automatique ou expirés';

    public function handle()
    {
        $this->info('Début de la génération des factures...');

        // Vérifier si l'option --expired est utilisée
        $isExpiredMode = $this->option('expired');
        
        if ($isExpiredMode) {
            $this->info('Mode abonnements expirés activé');
            // Récupérer les abonnements expirés qui n'ont pas encore de facture pour la période suivante
            $abonnements = Abonnement::where('statut', 'expire')
                ->where('renouvellement_automatique', true)
                ->where('facture_automatique', true)
                ->where('date_fin', '<', now())
                ->get();
        } else {
            // Récupérer les abonnements actifs avec renouvellement automatique
            // qui arrivent à échéance dans les 7 prochains jours
            $abonnements = Abonnement::where('statut', 'actif')
                ->where('renouvellement_automatique', true)
                ->where('facture_automatique', true)
                ->whereBetween('date_fin', [
                    now(),
                    now()->addDays(7)
                ])
                ->whereDoesntHave('facturations', function ($query) {
                    $query->where('date_facturation', '>=', now());
                })
                ->get();
        }

        $count = 0;
        foreach ($abonnements as $abonnement) {
            // Calculer la nouvelle période
            $nouvelleDate = Carbon::parse($abonnement->date_fin);
            $dateDebut = $nouvelleDate->copy();
            $dateFin = match($abonnement->type_periode) {
                'mensuel' => $nouvelleDate->addMonth(),
                'trimestriel' => $nouvelleDate->addMonths(3),
                'semestriel' => $nouvelleDate->addMonths(6),
                'annuel' => $nouvelleDate->addYear(),
                default => $nouvelleDate->addMonth()
            };

            // Générer un numéro de facture unique
            $numeroFacture = 'FAC-' . date('Y') . '-' . Str::random(8);
            
            // Créer la facture
            $facture = Facturation::create([
                'abonnement_id' => $abonnement->id,
                'entreprise_id' => $abonnement->entreprise_id,
                'numero_facture' => $numeroFacture,
                'date_facturation' => now(),
                'date_echeance' => now()->addDays(15),
                'montant_ht' => $abonnement->montant,
                'taux_tva' => 18.00,
                'montant_tva' => $abonnement->montant * 0.18,
                'montant_ttc' => $abonnement->montant * 1.18,
                'statut_paiement' => 'en_attente',
//                'mode_paiement' => $abonnement->mode_paiement ?? 'non_specifie',
                'notes' => $this->option('expired') 
                    ? "Renouvellement d'abonnement expiré - {$abonnement->planAbonnement->nom}" 
                    : "Renouvellement automatique - {$abonnement->planAbonnement->nom}",
                'devise' => 'XOF'
            ]);
            
            $this->info("Facture {$numeroFacture} créée pour l'abonnement {$abonnement->id}");
            
            // Si l'abonnement est expiré, mettre à jour son statut en 'en_attente'
            if ($this->option('expired') && $abonnement->statut === 'expire') {
                $abonnement->update([
                    'statut' => 'en_attente',
                    'derniere_facture_id' => $facture->id
                ]);
                $this->info("Statut de l'abonnement {$abonnement->id} mis à jour: 'en_attente'");
            }

            $count++;
        }

        $this->info("Génération terminée. {$count} factures générées.");
    }
}
