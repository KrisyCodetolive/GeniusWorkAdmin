<?php

namespace App\Services\Visite;

use App\Models\Visiteur;
use App\Models\Visite;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Exception;

class VisiteurService
{
    /**
     * Recherche un visiteur par son numéro de téléphone et son entreprise.
     *
     * @param string $telephone
     * @param string $entrepriseId
     * @return Visiteur|null
     */
    public function findVisiteurByTelephone($telephone, $entrepriseId)
    {
        return Visiteur::findByTelephone($telephone, $entrepriseId);
    }

    /**
     * Recherche un visiteur par son code visiteur.
     *
     * @param string $code
     * @return Visiteur|null
     */
    public function findVisiteurByCode($code)
    {
        return Visiteur::findByCode($code);
    }

    /**
     * Crée un nouveau visiteur.
     *
     * @param array $data
     * @return Visiteur
     */
    public function createVisiteur(array $data)
    {
        try {
            DB::beginTransaction();

            // Les IDs sont maintenant des UUIDs, nous n'avons plus besoin de les convertir en entiers

            // Vérifier si le visiteur existe déjà avec ce numéro de téléphone dans la même entreprise
            $visiteur = $this->findVisiteurByTelephone($data['telephone'], $data['entreprise_id']);
            
            if ($visiteur) {
                // Si le visiteur existe déjà, retourner le visiteur existant
                DB::commit();
                Log::info('Visiteur existant trouvé: ' . $visiteur->id);
                return $visiteur;
            }

            // Créer un nouveau visiteur
            Log::info('Création du visiteur: ' . json_encode($data));
            
            // La génération d'UUID est maintenant gérée automatiquement par le trait HasUuids
            
            $visiteur = Visiteur::create($data);
            
            Log::info('Visiteur créé avec succès: ' . $visiteur->id);
            
            DB::commit();
            return $visiteur;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la création du visiteur: ' . $e->getMessage() . '\nTrace: ' . $e->getTraceAsString());
            throw $e;
        }
    }

    /**
     * Met à jour un visiteur existant.
     *
     * @param Visiteur $visiteur
     * @param array $data
     * @return Visiteur
     */
    public function updateVisiteur(Visiteur $visiteur, array $data)
    {
        try {
            DB::beginTransaction();
            
            $visiteur->update($data);
            
            DB::commit();
            return $visiteur;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la mise à jour du visiteur: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Désactive un visiteur.
     *
     * @param Visiteur $visiteur
     * @return Visiteur
     */
    public function disableVisiteur(Visiteur $visiteur)
    {
        return $this->updateVisiteur($visiteur, ['statut' => 'inactif']);
    }

    /**
     * Active un visiteur.
     *
     * @param Visiteur $visiteur
     * @return Visiteur
     */
    public function enableVisiteur(Visiteur $visiteur)
    {
        return $this->updateVisiteur($visiteur, ['statut' => 'actif']);
    }

    /**
     * Génère un nouveau code visiteur unique.
     *
     * @return string
     */
    public function generateVisiteurCode()
    {
        return Visiteur::generateUniqueVisitorCode();
    }
}
