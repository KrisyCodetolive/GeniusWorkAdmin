<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Site;
use App\Models\Entreprise;

class SiteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $entreprises = Entreprise::all();

        foreach ($entreprises as $entreprise) {
            // Siège social
            Site::create([
                'entreprise_id' => $entreprise->id,
                'nom' => 'Siège Social',
                'adresse' => $entreprise->adresse ?? '1 rue Principale',
                'code_postal' => $entreprise->code_postal ?? '75000',
                'ville' => $entreprise->ville ?? 'Paris',
                'pays' => 'France',
                'latitude' => 48.8566,
                'longitude' => 2.3522,
                'rayon_geofencing' => 100,
                'has_geofencing' => true,
                'statut' => 'actif',
                'description' => 'Siège social de l\'entreprise',
                'horaires' => json_encode([
                    'lundi' => ['08:30-12:30', '13:30-17:30'],
                    'mardi' => ['08:30-12:30', '13:30-17:30'],
                    'mercredi' => ['08:30-12:30', '13:30-17:30'],
                    'jeudi' => ['08:30-12:30', '13:30-17:30'],
                    'vendredi' => ['08:30-12:30', '13:30-17:00'],
                    'samedi' => [],
                    'dimanche' => []
                ]),
                'contact_nom' => $entreprise->contact_nom ?? 'Responsable Site',
                'contact_email' => $entreprise->contact_email ?? 'contact@' . strtolower(str_replace(' ', '', $entreprise->nom)) . '.com',
                'contact_telephone' => $entreprise->contact_telephone ?? '01 23 45 67 89'
            ]);

            // Site secondaire (pour les entreprises de plus de 10 employés)
            if ($entreprise->users()->count() > 10) {
                Site::create([
                    'entreprise_id' => $entreprise->id,
                    'nom' => 'Site Secondaire',
                    'adresse' => '2 avenue des Champs',
                    'code_postal' => '69000',
                    'ville' => 'Lyon',
                    'pays' => 'France',
                    'latitude' => 45.7640,
                    'longitude' => 4.8357,
                    'rayon_geofencing' => 80,
                    'has_geofencing' => true,
                    'statut' => 'actif',
                    'description' => 'Site secondaire de l\'entreprise',
                    'horaires' => json_encode([
                        'lundi' => ['09:00-12:00', '14:00-18:00'],
                        'mardi' => ['09:00-12:00', '14:00-18:00'],
                        'mercredi' => ['09:00-12:00', '14:00-18:00'],
                        'jeudi' => ['09:00-12:00', '14:00-18:00'],
                        'vendredi' => ['09:00-12:00', '14:00-17:00'],
                        'samedi' => [],
                        'dimanche' => []
                    ]),
                    'contact_nom' => 'Responsable Lyon',
                    'contact_email' => 'lyon@' . strtolower(str_replace(' ', '', $entreprise->nom)) . '.com',
                    'contact_telephone' => '04 72 00 00 00'
                ]);
            }

            // Site de production (pour les entreprises manufacturières)
            if (in_array($entreprise->secteur_activite, ['Industrie', 'Production', 'Manufacture', 'Agroalimentaire'])) {
                Site::create([
                    'entreprise_id' => $entreprise->id,
                    'nom' => 'Usine de Production',
                    'adresse' => 'Zone Industrielle Est',
                    'code_postal' => '44000',
                    'ville' => 'Nantes',
                    'pays' => 'France',
                    'latitude' => 47.2184,
                    'longitude' => -1.5536,
                    'rayon_geofencing' => 150,
                    'has_geofencing' => true,
                    'statut' => 'actif',
                    'description' => 'Site de production principal',
                    'horaires' => json_encode([
                        'lundi' => ['06:00-14:00', '14:00-22:00', '22:00-06:00'],
                        'mardi' => ['06:00-14:00', '14:00-22:00', '22:00-06:00'],
                        'mercredi' => ['06:00-14:00', '14:00-22:00', '22:00-06:00'],
                        'jeudi' => ['06:00-14:00', '14:00-22:00', '22:00-06:00'],
                        'vendredi' => ['06:00-14:00', '14:00-22:00', '22:00-06:00'],
                        'samedi' => ['06:00-14:00'],
                        'dimanche' => []
                    ]),
                    'contact_nom' => 'Directeur de Production',
                    'contact_email' => 'production@' . strtolower(str_replace(' ', '', $entreprise->nom)) . '.com',
                    'contact_telephone' => '02 40 00 00 00'
                ]);
            }
        }
    }
}
