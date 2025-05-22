<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Entreprise;

class EntrepriseSeeder extends Seeder
{
    public function run()
    {
        $entreprises = [
            [
                'nom' => 'TechCorp Solutions',
                'code' => 'TCS',
                'email' => 'contact@techcorp.com',
                'telephone' => '+22501020304',
                'site_web' => 'https://techcorp.com',
                'devise' => 'XOF',
                'raison_sociale' => 'TechCorp Solutions SARL',
                'secteur_activite' => 'Technologies',
                'nombre_employes' => 50,
                'description' => 'Entreprise leader dans les solutions technologiques',
                'statut' => 'actif',
                'configuration' => json_encode([
                    'fuseau_horaire' => 'Africa/Abidjan',
                    'devise' => 'XOF',
                    'langue' => 'fr',
                    'jours_travail' => ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi'],
                ]),
                'parametres_presence' => json_encode([
                    'tolerance_retard' => 15,
                    'tolerance_depart' => 5
                ])
            ],
            [
                'nom' => 'Global Trading SA',
                'code' => 'GTS',
                'email' => 'info@globaltrading.com',
                'telephone' => '+22507080910',
                'site_web' => 'https://globaltrading.com',
                'devise' => 'XOF',
                'raison_sociale' => 'Global Trading SA',
                'secteur_activite' => 'Commerce',
                'nombre_employes' => 100,
                'description' => 'Leader dans le commerce international',
                'statut' => 'actif',
                'configuration' => json_encode([
                    'fuseau_horaire' => 'Africa/Abidjan',
                    'devise' => 'XOF',
                    'langue' => 'fr',
                    'jours_travail' => ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'],
                ]),
                'parametres_presence' => json_encode([
                    'tolerance_retard' => 10,
                    'tolerance_depart' => 5
                ])
            ]
        ];

        foreach ($entreprises as $entreprise) {
            Entreprise::create($entreprise);
        }
    }
}
