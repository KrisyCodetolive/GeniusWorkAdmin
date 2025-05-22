<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PlanAbonnement;

class PlanAbonnementSeeder extends Seeder
{
    public function run()
    {
        $plans = [
            [
                'nom' => 'Starter',
                'description' => 'Idéal pour les petites entreprises de 1 à 50 employés',
                'prix_mensuel' => 10000,
                'prix_annuel' => 108000,
                'duree_essai' => 14,
                'nombre_employes_min' => 1,
                'nombre_employes_max' => 50,
                'cout_par_employe' => 100,
                'devise' => 'XOF',
                'priorite' => 1,
                'statut' => 'actif',
                'fonctionnalites' => json_encode([
                    'gestion_employes' => [
                        'active' => true,
                        'limite' => 50,
                        'description' => 'Gestion des employés avec profils détaillés'
                    ],
                    'pointage_mobile' => [
                        'active' => true,
                        'description' => 'Pointage via application mobile'
                    ],
                    'rapports_base' => [
                        'active' => true,
                        'description' => 'Rapports de présence basiques'
                    ],
                    'support_email' => [
                        'active' => true,
                        'description' => 'Support par email'
                    ]
                ])
            ],
            [
                'nom' => 'Business',
                'description' => 'Pour les entreprises moyennes de 51 à 200 employés',
                'prix_mensuel' => 25000,
                'prix_annuel' => 270000,
                'duree_essai' => 14,
                'nombre_employes_min' => 51,
                'nombre_employes_max' => 100,
                'cout_par_employe' => 100,
                'devise' => 'XOF',
                'priorite' => 2,
                'statut' => 'actif',
                'fonctionnalites' => json_encode([
                    'gestion_employes' => [
                        'active' => true,
                        'limite' => 100,
                        'description' => 'Gestion des employés avec profils détaillés'
                    ],
                    'pointage_mobile' => [
                        'active' => true,
                        'description' => 'Pointage via application mobile'
                    ],
                    'rapports_avances' => [
                        'active' => true,
                        'description' => 'Rapports et analyses avancés'
                    ],
                    'support_prioritaire' => [
                        'active' => true,
                        'description' => 'Support prioritaire par email et téléphone'
                    ],
                    'gestion_conges' => [
                        'active' => true,
                        'description' => 'Gestion complète des congés'
                    ],
                    'planning_equipe' => [
                        'active' => true,
                        'description' => 'Planning d\'équipe'
                    ]
                ])
            ],
            [
                'nom' => 'Entreprise',
                'description' => 'Solution complète pour les grandes entreprises',
                'prix_mensuel' => 50000,
                'prix_annuel' => 540000,
                'duree_essai' => 14,
                'nombre_employes_min' => 101,
                'nombre_employes_max' => 200,
                'cout_par_employe' => 100,
                'devise' => 'XOF',
                'priorite' => 3,
                'statut' => 'actif',
                'fonctionnalites' => json_encode([
                    'gestion_employes' => [
                        'active' => true,
                        'limite' => null,
                        'description' => 'Gestion des employés avec profils détaillés'
                    ],
                    'pointage_multiple' => [
                        'active' => true,
                        'description' => 'Toutes les méthodes de pointage (Mobile, QR, Biométrique)'
                    ],
                    'rapports_personnalises' => [
                        'active' => true,
                        'description' => 'Rapports personnalisables et exports automatiques'
                    ],
                    'support_dedie' => [
                        'active' => true,
                        'description' => 'Support dédié 24/7'
                    ],
                    'gestion_conges' => [
                        'active' => true,
                        'description' => 'Gestion avancée des congés et absences'
                    ],
                    'planning_avance' => [
                        'active' => true,
                        'description' => 'Planning multi-équipes'
                    ],
                    'api_integration' => [
                        'active' => true,
                        'description' => 'API pour intégrations personnalisées'
                    ],
                    'multi_sites' => [
                        'active' => true,
                        'description' => 'Gestion multi-sites'
                    ]
                ])
            ]
        ];

        foreach ($plans as $plan) {
            PlanAbonnement::create($plan);
        }
    }
}
