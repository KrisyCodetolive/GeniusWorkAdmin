<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TypeConge;
use App\Models\Entreprise;

class TypeCongeSeeder extends Seeder
{
    public function run()
    {
        $entreprises = Entreprise::all();

        foreach ($entreprises as $entreprise) {
            $typesConges = [
                [
                    'entreprise_id' => $entreprise->id,
                    'nom' => 'Congé annuel',
                    'description' => 'Congé annuel payé',
                    'duree_max_annuelle' => 30,
                    'necessite_justificatif' => false,
                    'est_paye' => true,
                    'deductible_solde' => true,
                    'delai_demande_prealable' => 7,
                    'conditions_eligibilite' => json_encode([
                        'anciennete_minimum' => 12,
                        'statut_requis' => ['CDI', 'CDD'],
                    ]),
                    'configuration' => json_encode([
                        'report_autorise' => true,
                        'max_jours_report' => 10,
                        'fractionnement_autorise' => true,
                        'min_jours_fraction' => 1
                    ])
                ],
                [
                    'entreprise_id' => $entreprise->id,
                    'nom' => 'Congé maladie',
                    'description' => 'Congé pour raison médicale',
                    'duree_max_annuelle' => null,
                    'necessite_justificatif' => true,
                    'est_paye' => true,
                    'deductible_solde' => false,
                    'delai_demande_prealable' => 1,
                    'conditions_eligibilite' => json_encode([
                        'anciennete_minimum' => 0,
                        'statut_requis' => ['CDI', 'CDD', 'Stage', 'Prestation']
                    ]),
                    'configuration' => json_encode([
                        'report_autorise' => false,
                        'justificatif_medical_requis' => true,
                        'delai_justificatif' => 48 // heures
                    ])
                ],
                [
                    'entreprise_id' => $entreprise->id,
                    'nom' => 'Congé maternité',
                    'description' => 'Congé de maternité',
                    'duree_max_annuelle' => null,
                    'necessite_justificatif' => true,
                    'est_paye' => true,
                    'deductible_solde' => false,
                    'delai_demande_prealable' => 30,
                    'conditions_eligibilite' => json_encode([
                        'anciennete_minimum' => 0,
                        'statut_requis' => ['CDI', 'CDD'],
                        'sexe' => 'F'
                    ]),
                    'configuration' => json_encode([
                        'duree_standard' => 98, // 14 semaines
                        'extension_multiple' => 14, // 2 semaines supplémentaires
                        'justificatif_medical_requis' => true
                    ])
                ]
            ];

            foreach ($typesConges as $typeConge) {
                TypeConge::create($typeConge);
            }
        }
    }
}
