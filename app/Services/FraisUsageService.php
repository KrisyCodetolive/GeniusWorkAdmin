<?php

namespace App\Services;

use App\Models\FraisUsage;
use App\Models\Entreprise;
use App\Models\Facturation;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class FraisUsageService
{
    /**
     * Crée un nouveau frais d'usage
     *
     * @param array $data Les données du frais d'usage
     * @return FraisUsage
     */
    public function creer(array $data): FraisUsage
    {
        // Calculer le montant total
        $data['montant_total'] = $data['quantite'] * $data['prix_unitaire'];
        $data['statut'] = 'non_facture';
        
        return FraisUsage::create($data);
    }
    
    /**
     * Récupère les frais d'usage non facturés pour une entreprise
     *
     * @param Entreprise $entreprise L'entreprise concernée
     * @param array $options Options de filtrage
     * @return Collection
     */
    public function getFraisNonFactures(Entreprise $entreprise, array $options = []): Collection
    {
        $query = FraisUsage::where('entreprise_id', $entreprise->id)
            ->whereNull('facturation_id');
        
        // Filtrer par type de frais
        if (isset($options['type_frais'])) {
            $query->where('type_frais', $options['type_frais']);
        }
        
        // Filtrer par période
        if (isset($options['date_debut'])) {
            $query->where('date_debut_periode', '>=', $options['date_debut']);
        }
        
        if (isset($options['date_fin'])) {
            $query->where('date_fin_periode', '<=', $options['date_fin']);
        }
        
        return $query->get();
    }
    
    /**
     * Facture une liste de frais d'usage
     *
     * @param Collection $fraisUsages Collection de frais d'usage
     * @param Facturation|null $facturation Facture existante (optionnel)
     * @param FacturationService $facturationService Service de facturation
     * @return Facturation
     */
    public function facturer(Collection $fraisUsages, ?Facturation $facturation = null, FacturationService $facturationService): Facturation
    {
        // Vérifier que tous les frais appartiennent à la même entreprise
        $entrepriseId = $fraisUsages->first()->entreprise_id;
        if ($fraisUsages->pluck('entreprise_id')->unique()->count() > 1) {
            throw new \InvalidArgumentException('Les frais d\'usage doivent appartenir à la même entreprise.');
        }
        
        // Si aucune facture n'est spécifiée, en créer une nouvelle
        if (!$facturation) {
            $entreprise = Entreprise::findOrFail($entrepriseId);
            
            $facturation = Facturation::create([
                'entreprise_id' => $entrepriseId,
                'numero_facture' => $facturationService->genererNumeroFacture($entreprise),
                'date_facturation' => Carbon::now(),
                'date_echeance' => Carbon::now()->addDays(30),
                'montant_ht' => 0,
                'taux_tva' => 20, // Taux par défaut
                'montant_tva' => 0,
                'montant_ttc' => 0,
                'statut_paiement' => 'impaye',
                'notes' => 'Facture de frais d\'usage',
                'devise' => $fraisUsages->first()->devise
            ]);
        }
        
        // Ajouter les frais à la facture
        $fraisArray = $fraisUsages->map(function ($frais) {
            return [
                'type_frais' => $frais->type_frais,
                'description' => $frais->description,
                'quantite' => $frais->quantite,
                'prix_unitaire' => $frais->prix_unitaire,
                'periode_debut' => $frais->periode_debut,
                'periode_fin' => $frais->periode_fin,
            ];
        })->toArray();
        
        $facturationService->ajouterFraisUsage($facturation, $fraisArray);
        
        // Mettre à jour les frais d'usage
        foreach ($fraisUsages as $frais) {
            $frais->update([
                'facturation_id' => $facturation->id,
                'statut' => 'facture'
            ]);
        }
        
        return $facturation;
    }
    
    /**
     * Calcule les statistiques d'utilisation pour une entreprise
     *
     * @param Entreprise $entreprise L'entreprise concernée
     * @param string $periode Période de calcul (mois, trimestre, annee)
     * @return array
     */
    public function calculerStatistiques(Entreprise $entreprise, string $periode = 'mois'): array
    {
        // Déterminer les dates de début et de fin selon la période
        $dateFin = Carbon::now();
        
        switch ($periode) {
            case 'mois':
                $dateDebut = Carbon::now()->startOfMonth();
                break;
            case 'trimestre':
                $dateDebut = Carbon::now()->startOfQuarter();
                break;
            case 'annee':
                $dateDebut = Carbon::now()->startOfYear();
                break;
            default:
                $dateDebut = Carbon::now()->startOfMonth();
        }
        
        $fraisUsages = FraisUsage::where('entreprise_id', $entreprise->id)
            ->where(function ($query) use ($dateDebut, $dateFin) {
                $query->whereBetween('periode_debut', [$dateDebut, $dateFin])
                    ->orWhereBetween('periode_fin', [$dateDebut, $dateFin]);
            })
            ->get();
        
        // Regrouper par type de frais
        $fraisParType = $fraisUsages->groupBy('type_frais');
        
        // Calculer les totaux par type
        $totauxParType = [];
        foreach ($fraisParType as $type => $frais) {
            $totauxParType[$type] = [
                'quantite' => $frais->sum('quantite'),
                'montant_total' => $frais->sum('montant_total'),
                'count' => $frais->count(),
            ];
        }
        
        // Calculer le total global
        $totalGlobal = $fraisUsages->sum('montant_total');
        
        // Calculer la tendance par rapport à la période précédente
        $periodesPrecedentes = [
            'mois' => Carbon::now()->subMonth()->startOfMonth(),
            'trimestre' => Carbon::now()->subQuarter()->startOfQuarter(),
            'annee' => Carbon::now()->subYear()->startOfYear(),
        ];
        
        $dateDebutPrecedente = $periodesPrecedentes[$periode] ?? Carbon::now()->subMonth()->startOfMonth();
        $dateFinPrecedente = $dateDebut->copy()->subDay();
        
        $fraisUsagesPrecedents = FraisUsage::where('entreprise_id', $entreprise->id)
            ->where(function ($query) use ($dateDebutPrecedente, $dateFinPrecedente) {
                $query->whereBetween('periode_debut', [$dateDebutPrecedente, $dateFinPrecedente])
                    ->orWhereBetween('periode_fin', [$dateDebutPrecedente, $dateFinPrecedente]);
            })
            ->get();
        
        $totalPrecedent = $fraisUsagesPrecedents->sum('montant_total');
        
        // Calculer la variation en pourcentage
        $variation = 0;
        if ($totalPrecedent > 0) {
            $variation = (($totalGlobal - $totalPrecedent) / $totalPrecedent) * 100;
        }
        
        return [
            'frais_par_type' => $fraisParType,
            'totaux_par_type' => $totauxParType,
            'total_global' => $totalGlobal,
            'total_precedent' => $totalPrecedent,
            'variation' => $variation,
            'periode' => $periode,
            'date_debut' => $dateDebut,
            'date_fin' => $dateFin,
        ];
    }
    
    /**
     * Génère des frais d'usage automatiques pour une entreprise (par exemple, SMS envoyés)
     *
     * @param Entreprise $entreprise L'entreprise concernée
     * @param string $typeFrais Type de frais (sms, email, etc.)
     * @param int $quantite Quantité utilisée
     * @param float $prixUnitaire Prix unitaire
     * @param string $description Description du frais
     * @return FraisUsage
     */
    public function genererFraisAutomatique(Entreprise $entreprise, string $typeFrais, int $quantite, float $prixUnitaire, string $description): FraisUsage
    {
        $debut = Carbon::now()->startOfMonth();
        $fin = Carbon::now()->endOfMonth();
        
        return $this->creer([
            'entreprise_id' => $entreprise->id,
            'type_frais' => $typeFrais,
            'description' => $description,
            'quantite' => $quantite,
            'prix_unitaire' => $prixUnitaire,
            'montant_total' => $quantite * $prixUnitaire,
            'periode_debut' => $debut,
            'periode_fin' => $fin,
            'statut' => 'non_facture',
            'devise' => 'EUR'
        ]);
    }
}
