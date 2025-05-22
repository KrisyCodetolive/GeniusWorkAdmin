<?php

namespace App\Services;

use App\Models\CodePromo;
use Illuminate\Support\Str;
use Carbon\Carbon;

class CodePromoService
{
    /**
     * Génère un nouveau code promo
     *
     * @param string|null $prefix Préfixe du code
     * @param int $length Longueur du code (sans préfixe)
     * @return string
     */
    public function genererCode(?string $prefix = null, int $length = 8): string
    {
        $prefix = $prefix ? strtoupper($prefix) . '-' : '';
        $code = $prefix . strtoupper(Str::random($length));
        
        // Vérifier que le code n'existe pas déjà
        while (CodePromo::where('code', $code)->exists()) {
            $code = $prefix . strtoupper(Str::random($length));
        }
        
        return $code;
    }
    
    /**
     * Crée un nouveau code promo
     *
     * @param array $data Données du code promo
     * @return CodePromo
     */
    public function creer(array $data): CodePromo
    {
        // Générer un code si non fourni
        if (!isset($data['code']) || empty($data['code'])) {
            $data['code'] = $this->genererCode(
                $data['prefix'] ?? null, 
                $data['length'] ?? 8
            );
        }
        
        // Valeurs par défaut
        $data['nombre_utilisations'] = $data['nombre_utilisations'] ?? 0;
        $data['actif'] = $data['actif'] ?? true;
        
        // Créer le code promo
        return CodePromo::create($data);
    }
    
    /**
     * Valide un code promo et retourne le montant de réduction
     *
     * @param string $code Code promo à valider
     * @param float $montant Montant sur lequel appliquer la réduction
     * @return array Tableau contenant le statut et les détails de la validation
     */
    public function valider(string $code, float $montant): array
    {
        $codePromo = CodePromo::where('code', $code)->first();
        
        if (!$codePromo) {
            return [
                'valide' => false,
                'message' => 'Code promo invalide ou inexistant',
                'reduction' => 0
            ];
        }
        
        if (!$codePromo->estValide()) {
            $raison = '';
            
            if (!$codePromo->actif) {
                $raison = 'Ce code promo n\'est plus actif';
            } elseif (now()->lt($codePromo->date_debut)) {
                $raison = 'Ce code promo n\'est pas encore valide';
            } elseif (now()->gt($codePromo->date_expiration)) {
                $raison = 'Ce code promo a expiré';
            } elseif ($codePromo->nombre_utilisations_max !== null && $codePromo->nombre_utilisations >= $codePromo->nombre_utilisations_max) {
                $raison = 'Ce code promo a atteint son nombre maximum d\'utilisations';
            }
            
            return [
                'valide' => false,
                'message' => $raison,
                'reduction' => 0
            ];
        }
        
        // Calculer la réduction
        $reduction = $montant * ($codePromo->reduction / 100);
        
        return [
            'valide' => true,
            'message' => 'Code promo valide',
            'reduction' => $reduction,
            'code_promo_id' => $codePromo->id,
            'pourcentage' => $codePromo->reduction
        ];
    }
    
    /**
     * Applique un code promo (incrémente son nombre d'utilisations)
     *
     * @param string $code Code promo à appliquer
     * @return bool
     */
    public function appliquer(string $code): bool
    {
        $codePromo = CodePromo::where('code', $code)->first();
        
        if (!$codePromo || !$codePromo->estValide()) {
            return false;
        }
        
        $codePromo->incrementerUtilisations();
        return true;
    }
    
    /**
     * Désactive un code promo
     *
     * @param string $code Code promo à désactiver
     * @return bool
     */
    public function desactiver(string $code): bool
    {
        $codePromo = CodePromo::where('code', $code)->first();
        
        if (!$codePromo) {
            return false;
        }
        
        $codePromo->update(['actif' => false]);
        return true;
    }
    
    /**
     * Prolonge la date d'expiration d'un code promo
     *
     * @param string $code Code promo à prolonger
     * @param int $jours Nombre de jours à ajouter
     * @return bool
     */
    public function prolonger(string $code, int $jours): bool
    {
        $codePromo = CodePromo::where('code', $code)->first();
        
        if (!$codePromo) {
            return false;
        }
        
        $nouvelleDate = Carbon::parse($codePromo->date_expiration)->addDays($jours);
        $codePromo->update(['date_expiration' => $nouvelleDate]);
        
        return true;
    }
}
