<?php

namespace App\Observers;

use App\Models\Abonnement;
use App\Models\Facturation;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class AbonnementObserver
{
    /**
     * Handle the Abonnement "created" event.
     */
    public function created(Abonnement $abonnement): void
    {
        $this->genererFacture($abonnement);
    }

    /**
     * Handle the Abonnement "updated" event.
     */
    public function updated(Abonnement $abonnement): void
    {
        // Si le statut vient d'être changé à 'expire', générer une nouvelle facture
        if ($abonnement->wasChanged('statut') && $abonnement->statut === 'expire') {
            $this->genererFacture($abonnement);
        }
    }

    /**
     * Générer une facture pour l'abonnement
     */
    protected function genererFacture(Abonnement $abonnement): void
    {
        try {
            // Générer un numéro de facture unique
            $numeroFacture = 'FAC-' . date('Y') . '-' . Str::random(8);
            
            // Calculer les dates de la facture
            $dateFacture = now();
            $dateEcheance = $dateFacture->copy()->addDays(15); // 15 jours pour payer

            // Calculer les montants
            $montantHT = $abonnement->montant;
            $tauxTVA = env('TVA', 0.18); // Taux de TVA depuis la configuration
            $montantTVA = $montantHT * $tauxTVA;
            $montantTTC = $montantHT * (1 + $tauxTVA);
            $reduction = $abonnement->reduction_code_promo ?? 0;
            $montantFinal = $montantTTC - $reduction;

            // Mapper la méthode de paiement aux valeurs acceptées par l'enum
            $modePaiement = 'non_specifie';
            if ($abonnement->mode_paiement === 'card') {
                $modePaiement = 'carte';
            } elseif ($abonnement->mode_paiement === 'bank_transfer') {
                $modePaiement = 'virement';
            } elseif ($abonnement->mode_paiement === 'cash') {
                $modePaiement = 'especes';
            }
            // Créer la facture
            Facturation::create([
                'abonnement_id' => $abonnement->id,
                'entreprise_id' => $abonnement->entreprise_id,
                'numero_facture' => $numeroFacture,
                'date_facturation' => $dateFacture,
                'date_facture' => $dateFacture,
                'date_echeance' => $dateEcheance,
                'montant_ht' => $montantHT,
                'montant_tva' => $montantTVA, // Correction du nom du champ
                'montant_ttc' => $montantTTC,
                'reduction' => $reduction,
                'montant_final' => $montantFinal,
                'statut' => 'en_attente',
                'mode_paiement' => $abonnement->mode_paiement ?? 'virement',
                'periode_debut' => $abonnement->date_debut,
                'periode_fin' => $abonnement->date_fin,
                'notes' => "Facture d'abonnement {$abonnement->planAbonnement->nom}",
                'meta_donnees' => json_encode([
                    'type_periode' => $abonnement->type_periode,
                    'plan_abonnement' => $abonnement->planAbonnement->nom,
                    'nombre_personnels' => $abonnement->nombre_personnels,
                    'taux_tva' => $tauxTVA * 100 . '%',
                    'details_calcul' => [
                        'montant_ht' => $montantHT,
                        'taux_tva' => $tauxTVA * 100 . '%',
                        'montant_tva' => $montantTVA,
                        'montant_ttc' => $montantTTC,
                        'reduction' => $reduction,
                        'montant_final' => $montantFinal
                    ]
                ])
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur lors de la génération de la facture', [
                'error' => $e->getMessage(),
                'abonnement_id' => $abonnement->id,
                'entreprise_id' => $abonnement->entreprise_id,
                'montants' => [
                    'ht' => $montantHT,
                    'tva' => $montantTVA,
                    'ttc' => $montantTTC,
                    'reduction' => $reduction,
                    'final' => $montantFinal
                ]
            ]);
            throw $e;
        }
    }
}
