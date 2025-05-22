<?php

namespace App\Services;

use App\Models\Facturation;
use App\Models\Abonnement;
use App\Models\Entreprise;
use App\Models\FraisUsage;
use Carbon\Carbon;
use Illuminate\Support\Str;

class FacturationService
{
    /**
     * Génère un numéro de facture unique
     *
     * @param Entreprise $entreprise L'entreprise concernée
     * @return string
     */
    public function genererNumeroFacture(Entreprise $entreprise): string
    {
        $prefix = $entreprise->code ?? 'FACT';
        $date = Carbon::now()->format('Ymd');
        $random = strtoupper(Str::random(4));
        
        $numero = "{$prefix}-{$date}-{$random}";
        
        // Vérifier que le numéro n'existe pas déjà
        while (Facturation::where('numero_facture', $numero)->exists()) {
            $random = strtoupper(Str::random(4));
            $numero = "{$prefix}-{$date}-{$random}";
        }
        
        return $numero;
    }
    
    /**
     * Crée une facture pour un abonnement
     *
     * @param Abonnement $abonnement L'abonnement à facturer
     * @param array $options Options supplémentaires
     * @return Facturation
     */
    public function creerFactureAbonnement(Abonnement $abonnement, array $options = []): Facturation
    {
        $entreprise = $abonnement->entreprise;
        $planAbonnement = $abonnement->planAbonnement;
        
        // Déterminer le montant HT
        $montantHT = $abonnement->montant;
        
        // Déterminer le taux de TVA (par défaut 20%)
        $tauxTVA = $options['taux_tva'] ?? 20;
        
        // Calculer la TVA et le montant TTC
        $montantTVA = $montantHT * ($tauxTVA / 100);
        $montantTTC = $montantHT + $montantTVA;
        
        // Créer la facture
        $facture = Facturation::create([
            'entreprise_id' => $entreprise->id,
            'abonnement_id' => $abonnement->id,
            'numero_facture' => $this->genererNumeroFacture($entreprise),
            'date_facturation' => Carbon::now(),
            'date_echeance' => Carbon::now()->addDays($options['delai_paiement'] ?? 30),
            'montant_ht' => $montantHT,
            'taux_tva' => $tauxTVA,
            'montant_tva' => $montantTVA,
            'montant_ttc' => $montantTTC,
            'statut_paiement' => 'en_attente',
            'mode_paiement' => $options['mode_paiement'] ?? 'non_specifie',
            'reference_paiement' => $options['reference_paiement'] ?? null,
            'notes' => $options['notes'] ?? "Facture d'abonnement {$planAbonnement->nom}",
            'devise' => $planAbonnement->devise ?? 'XOF'
        ]);
        
        return $facture;
    }
    
    /**
     * Ajoute des frais d'usage à une facture existante
     *
     * @param Facturation $facture La facture concernée
     * @param array $fraisUsage Les frais d'usage à ajouter
     * @return Facturation
     */
    public function ajouterFraisUsage(Facturation $facture, array $fraisUsage): Facturation
    {
        $montantTotalHT = 0;
        
        foreach ($fraisUsage as $frais) {
            // Créer l'entrée de frais d'usage
            $fraisModel = FraisUsage::create([
                'entreprise_id' => $facture->entreprise_id,
                'facturation_id' => $facture->id,
                'type_frais' => $frais['type_frais'],
                'description' => $frais['description'],
                'quantite' => $frais['quantite'],
                'prix_unitaire' => $frais['prix_unitaire'],
                'montant_total' => $frais['quantite'] * $frais['prix_unitaire'],
                'periode_debut' => $frais['periode_debut'] ?? null,
                'periode_fin' => $frais['periode_fin'] ?? null,
                'statut' => 'facture',
                'devise' => $facture->devise
            ]);
            
            $montantTotalHT += $fraisModel->montant_total;
        }
        
        // Mettre à jour les montants de la facture
        $nouveauMontantHT = $facture->montant_ht + $montantTotalHT;
        $nouveauMontantTVA = $nouveauMontantHT * ($facture->taux_tva / 100);
        $nouveauMontantTTC = $nouveauMontantHT + $nouveauMontantTVA;
        
        $facture->update([
            'montant_ht' => $nouveauMontantHT,
            'montant_tva' => $nouveauMontantTVA,
            'montant_ttc' => $nouveauMontantTTC
        ]);
        
        return $facture;
    }
    
    /**
     * Marque une facture comme payée
     *
     * @param Facturation $facture La facture à marquer comme payée
     * @param array $options Options de paiement
     * @return Facturation
     */
    public function marquerCommePaye(Facturation $facture, array $options = []): Facturation
    {
        $facture->update([
            'statut_paiement' => 'paye',
            'mode_paiement' => $options['mode_paiement'] ?? $facture->mode_paiement,
            'reference_paiement' => $options['reference_paiement'] ?? $facture->reference_paiement,
            'notes' => $options['notes'] ?? $facture->notes
        ]);
        
        return $facture;
    }
    
    /**
     * Génère les factures pour tous les abonnements actifs qui doivent être facturés
     *
     * @return array Tableau des factures générées
     */
    public function genererFacturesAutomatiques(): array
    {
        $facturesGenerees = [];
        
        // Récupérer tous les abonnements actifs avec facture_automatique=true
        $abonnements = Abonnement::where('statut', 'actif')
            ->where('facture_automatique', true)
            ->get();
        
        foreach ($abonnements as $abonnement) {
            // Vérifier si une facture a déjà été générée ce mois-ci
            $debutMois = Carbon::now()->startOfMonth();
            $finMois = Carbon::now()->endOfMonth();
            
            $factureExistante = Facturation::where('abonnement_id', $abonnement->id)
                ->whereBetween('date_facturation', [$debutMois, $finMois])
                ->exists();
            
            if (!$factureExistante) {
                // Générer une nouvelle facture
                $facture = $this->creerFactureAbonnement($abonnement, [
                    'notes' => "Facture automatique - {$abonnement->planAbonnement->nom}"
                ]);
                
                $facturesGenerees[] = $facture;
            }
        }
        
        return $facturesGenerees;
    }
    
    /**
     * Envoie une facture par email
     *
     * @param Facturation $facture La facture à envoyer
     * @param string|null $email Email de destination (si null, utilise l'email de l'entreprise)
     * @return bool
     */
    public function envoyerFactureParEmail(Facturation $facture, ?string $email = null): bool
    {
        $email = $email ?? $facture->entreprise->email;
        
        if (!$email) {
            return false;
        }
        
        // Logique d'envoi d'email (à implémenter selon le système d'emails de l'application)
        // ...
        
        return true;
    }
}
