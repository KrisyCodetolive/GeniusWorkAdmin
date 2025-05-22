<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\TypeConge;
use App\Models\Entreprise;

class TypeCongeExempleSeeder extends Seeder
{
    /**
     * Liste des types de congés basiques à créer comme exemples
     */
    public static function getTypesCongeExemples()
    {
        return [
            [
                'nom' => 'Congé annuel',
                'description' => 'Congé annuel payé standard',
                'duree_max_annuelle' => 30,
                'necessite_justificatif' => false,
                'est_paye' => true,
                'deductible_solde' => true,
                'delai_demande_prealable' => 7,
                'statut' => 'actif',
                'conditions_eligibilite' => null,
                'configuration' => null,
            ],
            [
                'nom' => 'Congé maladie',
                'description' => 'Congé pour raison médicale',
                'duree_max_annuelle' => null,
                'necessite_justificatif' => true,
                'est_paye' => true,
                'deductible_solde' => false,
                'delai_demande_prealable' => 1,
                'statut' => 'actif',
                'conditions_eligibilite' => null,
                'configuration' => null,
            ],
            [
                'nom' => 'Congé maternité',
                'description' => 'Congé pour les femmes avant et après l\'accouchement',
                'duree_max_annuelle' => 98, // 14 semaines
                'necessite_justificatif' => true,
                'est_paye' => true,
                'deductible_solde' => false,
                'delai_demande_prealable' => 30,
                'statut' => 'actif',
                'conditions_eligibilite' => null,
                'configuration' => null,
            ],
            [
                'nom' => 'Congé paternité',
                'description' => 'Congé accordé aux pères à la naissance de leur enfant',
                'duree_max_annuelle' => 14,
                'necessite_justificatif' => true,
                'est_paye' => true,
                'deductible_solde' => false,
                'delai_demande_prealable' => 15,
                'statut' => 'actif',
                'conditions_eligibilite' => null,
                'configuration' => null,
            ],
            [
                'nom' => 'Congé sans solde',
                'description' => 'Congé non rémunéré pour convenances personnelles',
                'duree_max_annuelle' => 90,
                'necessite_justificatif' => false,
                'est_paye' => false,
                'deductible_solde' => false,
                'delai_demande_prealable' => 30,
                'statut' => 'actif',
                'conditions_eligibilite' => null,
                'configuration' => null,
            ],
            [
                'nom' => 'Congé pour événement familial',
                'description' => 'Congé pour mariage, décès, naissance dans la famille',
                'duree_max_annuelle' => 10,
                'necessite_justificatif' => true,
                'est_paye' => true,
                'deductible_solde' => false,
                'delai_demande_prealable' => 3,
                'statut' => 'actif',
                'conditions_eligibilite' => null,
                'configuration' => null,
            ],
            [
                'nom' => 'Congé de formation',
                'description' => 'Congé pour suivre une formation professionnelle',
                'duree_max_annuelle' => 20,
                'necessite_justificatif' => true,
                'est_paye' => true,
                'deductible_solde' => false,
                'delai_demande_prealable' => 15,
                'statut' => 'actif',
                'conditions_eligibilite' => null,
                'configuration' => null,
            ],
        ];
    }

    /**
     * Seed the application's database with example leave types.
     */
    public function run()
    {
        // Cette méthode n'est pas utilisée directement
        // Nous utilisons plutôt la méthode createForEntreprise
    }

    /**
     * Crée des types de congés exemples pour une entreprise spécifique
     */
    public static function createForEntreprise(Entreprise $entreprise)
    {
        $typesConge = self::getTypesCongeExemples();
        $created = [];
        $existants = 0;

        foreach ($typesConge as $typeConge) {
            // Vérifier si un type de congé similaire existe déjà pour cette entreprise
            $existant = TypeConge::where('entreprise_id', $entreprise->id)
                ->where('nom', $typeConge['nom'])
                ->first();
            
            // Ne créer que s'il n'existe pas déjà
            if (!$existant) {
                $typeConge['entreprise_id'] = $entreprise->id;
                $created[] = TypeConge::create($typeConge);
            } else {
                $existants++;
            }
        }

        return [
            'created' => $created,
            'existants' => $existants,
            'total' => count($typesConge)
        ];
    }
}
