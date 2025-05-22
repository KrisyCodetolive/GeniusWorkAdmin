<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\PlageHoraire;
use App\Models\Entreprise;
use Carbon\Carbon;

class PlageHoraireExempleSeeder extends Seeder
{
    /**
     * Liste des plages horaires basiques à créer comme exemples
     */
    public static function getPlagesHorairesExemples()
    {
        return [
            [
                'nom' => 'Standard (8h-17h)',
                'description' => 'Horaire standard de bureau avec pause déjeuner d\'une heure',
                'heure_debut' => '08:00',
                'heure_fin' => '17:00',
                'duree_pause' => 60,
                'est_standard' => true,
                'est_flexible' => false,
                'marge_retard' => 15,
                'marge_depart' => 0,
                'statut' => 'actif',
                'jours_travail' => [
                    [
                        'jour_semaine' => 'Lundi',
                        'est_travaille' => true,
                        'est_ferie' => false,
                        'heure_debut' => '08:00',
                        'heure_fin' => '17:00',
                    ],
                    [
                        'jour_semaine' => 'Mardi',
                        'est_travaille' => true,
                        'est_ferie' => false,
                        'heure_debut' => '08:00',
                        'heure_fin' => '17:00',
                    ],
                    [
                        'jour_semaine' => 'Mercredi',
                        'est_travaille' => true,
                        'est_ferie' => false,
                        'heure_debut' => '08:00',
                        'heure_fin' => '17:00',
                    ],
                    [
                        'jour_semaine' => 'Jeudi',
                        'est_travaille' => true,
                        'est_ferie' => false,
                        'heure_debut' => '08:00',
                        'heure_fin' => '17:00',
                    ],
                    [
                        'jour_semaine' => 'Vendredi',
                        'est_travaille' => true,
                        'est_ferie' => false,
                        'heure_debut' => '08:00',
                        'heure_fin' => '17:00',
                    ],
                    [
                        'jour_semaine' => 'Samedi',
                        'est_travaille' => false,
                        'est_ferie' => false,
                        'heure_debut' => '08:00',
                        'heure_fin' => '17:00',
                    ],
                    [
                        'jour_semaine' => 'Dimanche',
                        'est_travaille' => false,
                        'est_ferie' => false,
                        'heure_debut' => '08:00',
                        'heure_fin' => '17:00',
                    ],
                ],
                'pauses' => [
                    'Déjeuner' => '60',
                ],
                'configuration' => null,
            ],
            [
                'nom' => 'Mi-temps (Matin)',
                'description' => 'Horaire de mi-temps pour la matinée',
                'heure_debut' => '08:00',
                'heure_fin' => '12:00',
                'duree_pause' => 15,
                'est_standard' => true,
                'est_flexible' => false,
                'marge_retard' => 10,
                'marge_depart' => 0,
                'statut' => 'actif',
                'jours_travail' => [
                    [
                        'jour_semaine' => 'Lundi',
                        'est_travaille' => true,
                        'est_ferie' => false,
                        'heure_debut' => '08:00',
                        'heure_fin' => '12:00',
                    ],
                    [
                        'jour_semaine' => 'Mardi',
                        'est_travaille' => true,
                        'est_ferie' => false,
                        'heure_debut' => '08:00',
                        'heure_fin' => '12:00',
                    ],
                    [
                        'jour_semaine' => 'Mercredi',
                        'est_travaille' => true,
                        'est_ferie' => false,
                        'heure_debut' => '08:00',
                        'heure_fin' => '12:00',
                    ],
                    [
                        'jour_semaine' => 'Jeudi',
                        'est_travaille' => true,
                        'est_ferie' => false,
                        'heure_debut' => '08:00',
                        'heure_fin' => '12:00',
                    ],
                    [
                        'jour_semaine' => 'Vendredi',
                        'est_travaille' => true,
                        'est_ferie' => false,
                        'heure_debut' => '08:00',
                        'heure_fin' => '12:00',
                    ],
                    [
                        'jour_semaine' => 'Samedi',
                        'est_travaille' => false,
                        'est_ferie' => false,
                        'heure_debut' => '08:00',
                        'heure_fin' => '12:00',
                    ],
                    [
                        'jour_semaine' => 'Dimanche',
                        'est_travaille' => false,
                        'est_ferie' => false,
                        'heure_debut' => '08:00',
                        'heure_fin' => '12:00',
                    ],
                ],
                'pauses' => [
                    'Pause' => '15',
                ],
                'configuration' => null,
            ],
            [
                'nom' => 'Mi-temps (Après-midi)',
                'description' => 'Horaire de mi-temps pour l\'après-midi',
                'heure_debut' => '13:00',
                'heure_fin' => '17:00',
                'duree_pause' => 15,
                'est_standard' => true,
                'est_flexible' => false,
                'marge_retard' => 10,
                'marge_depart' => 0,
                'statut' => 'actif',
                'jours_travail' => [
                    [
                        'jour_semaine' => 'Lundi',
                        'est_travaille' => true,
                        'est_ferie' => false,
                        'heure_debut' => '13:00',
                        'heure_fin' => '17:00',
                    ],
                    [
                        'jour_semaine' => 'Mardi',
                        'est_travaille' => true,
                        'est_ferie' => false,
                        'heure_debut' => '13:00',
                        'heure_fin' => '17:00',
                    ],
                    [
                        'jour_semaine' => 'Mercredi',
                        'est_travaille' => true,
                        'est_ferie' => false,
                        'heure_debut' => '13:00',
                        'heure_fin' => '17:00',
                    ],
                    [
                        'jour_semaine' => 'Jeudi',
                        'est_travaille' => true,
                        'est_ferie' => false,
                        'heure_debut' => '13:00',
                        'heure_fin' => '17:00',
                    ],
                    [
                        'jour_semaine' => 'Vendredi',
                        'est_travaille' => true,
                        'est_ferie' => false,
                        'heure_debut' => '13:00',
                        'heure_fin' => '17:00',
                    ],
                    [
                        'jour_semaine' => 'Samedi',
                        'est_travaille' => false,
                        'est_ferie' => false,
                        'heure_debut' => '13:00',
                        'heure_fin' => '17:00',
                    ],
                    [
                        'jour_semaine' => 'Dimanche',
                        'est_travaille' => false,
                        'est_ferie' => false,
                        'heure_debut' => '13:00',
                        'heure_fin' => '17:00',
                    ],
                ],
                'pauses' => [
                    'Pause' => '15',
                ],
                'configuration' => null,
            ],
            [
                'nom' => 'Horaire flexible',
                'description' => 'Horaire flexible avec plage fixe de 10h à 15h',
                'heure_debut' => '08:00',
                'heure_fin' => '18:00',
                'duree_pause' => 60,
                'est_standard' => false,
                'est_flexible' => true,
                'marge_retard' => 30,
                'marge_depart' => 30,
                'statut' => 'actif',
                'jours_travail' => [
                    [
                        'jour_semaine' => 'Lundi',
                        'est_travaille' => true,
                        'est_ferie' => false,
                        'heure_debut' => '08:00',
                        'heure_fin' => '18:00',
                    ],
                    [
                        'jour_semaine' => 'Mardi',
                        'est_travaille' => true,
                        'est_ferie' => false,
                        'heure_debut' => '08:00',
                        'heure_fin' => '18:00',
                    ],
                    [
                        'jour_semaine' => 'Mercredi',
                        'est_travaille' => true,
                        'est_ferie' => false,
                        'heure_debut' => '08:00',
                        'heure_fin' => '18:00',
                    ],
                    [
                        'jour_semaine' => 'Jeudi',
                        'est_travaille' => true,
                        'est_ferie' => false,
                        'heure_debut' => '08:00',
                        'heure_fin' => '18:00',
                    ],
                    [
                        'jour_semaine' => 'Vendredi',
                        'est_travaille' => true,
                        'est_ferie' => false,
                        'heure_debut' => '08:00',
                        'heure_fin' => '18:00',
                    ],
                    [
                        'jour_semaine' => 'Samedi',
                        'est_travaille' => false,
                        'est_ferie' => false,
                        'heure_debut' => '08:00',
                        'heure_fin' => '18:00',
                    ],
                    [
                        'jour_semaine' => 'Dimanche',
                        'est_travaille' => false,
                        'est_ferie' => false,
                        'heure_debut' => '08:00',
                        'heure_fin' => '18:00',
                    ],
                ],
                'pauses' => [
                    'Déjeuner' => '60',
                ],
                'configuration' => [
                    'plage_fixe_debut' => '10:00',
                    'plage_fixe_fin' => '15:00',
                ],
            ],
            [
                'nom' => 'Travail de nuit',
                'description' => 'Horaire pour le travail de nuit',
                'heure_debut' => '22:00',
                'heure_fin' => '06:00',
                'duree_pause' => 30,
                'est_standard' => true,
                'est_flexible' => false,
                'marge_retard' => 10,
                'marge_depart' => 0,
                'statut' => 'actif',
                'jours_travail' => [
                    [
                        'jour_semaine' => 'Lundi',
                        'est_travaille' => true,
                        'est_ferie' => false,
                        'heure_debut' => '22:00',
                        'heure_fin' => '06:00',
                    ],
                    [
                        'jour_semaine' => 'Mardi',
                        'est_travaille' => true,
                        'est_ferie' => false,
                        'heure_debut' => '22:00',
                        'heure_fin' => '06:00',
                    ],
                    [
                        'jour_semaine' => 'Mercredi',
                        'est_travaille' => true,
                        'est_ferie' => false,
                        'heure_debut' => '22:00',
                        'heure_fin' => '06:00',
                    ],
                    [
                        'jour_semaine' => 'Jeudi',
                        'est_travaille' => true,
                        'est_ferie' => false,
                        'heure_debut' => '22:00',
                        'heure_fin' => '06:00',
                    ],
                    [
                        'jour_semaine' => 'Vendredi',
                        'est_travaille' => true,
                        'est_ferie' => false,
                        'heure_debut' => '22:00',
                        'heure_fin' => '06:00',
                    ],
                    [
                        'jour_semaine' => 'Samedi',
                        'est_travaille' => false,
                        'est_ferie' => false,
                        'heure_debut' => '22:00',
                        'heure_fin' => '06:00',
                    ],
                    [
                        'jour_semaine' => 'Dimanche',
                        'est_travaille' => false,
                        'est_ferie' => false,
                        'heure_debut' => '22:00',
                        'heure_fin' => '06:00',
                    ],
                ],
                'pauses' => [
                    'Pause repas' => '30',
                ],
                'configuration' => null,
            ],
            [
                'nom' => 'Horaire weekend',
                'description' => 'Horaire spécifique pour le travail de weekend',
                'heure_debut' => '10:00',
                'heure_fin' => '18:00',
                'duree_pause' => 45,
                'est_standard' => true,
                'est_flexible' => false,
                'marge_retard' => 15,
                'marge_depart' => 0,
                'statut' => 'actif',
                'jours_travail' => [
                    [
                        'jour_semaine' => 'Lundi',
                        'est_travaille' => false,
                        'est_ferie' => false,
                        'heure_debut' => '10:00',
                        'heure_fin' => '18:00',
                    ],
                    [
                        'jour_semaine' => 'Mardi',
                        'est_travaille' => false,
                        'est_ferie' => false,
                        'heure_debut' => '10:00',
                        'heure_fin' => '18:00',
                    ],
                    [
                        'jour_semaine' => 'Mercredi',
                        'est_travaille' => false,
                        'est_ferie' => false,
                        'heure_debut' => '10:00',
                        'heure_fin' => '18:00',
                    ],
                    [
                        'jour_semaine' => 'Jeudi',
                        'est_travaille' => false,
                        'est_ferie' => false,
                        'heure_debut' => '10:00',
                        'heure_fin' => '18:00',
                    ],
                    [
                        'jour_semaine' => 'Vendredi',
                        'est_travaille' => false,
                        'est_ferie' => false,
                        'heure_debut' => '10:00',
                        'heure_fin' => '18:00',
                    ],
                    [
                        'jour_semaine' => 'Samedi',
                        'est_travaille' => true,
                        'est_ferie' => false,
                        'heure_debut' => '10:00',
                        'heure_fin' => '18:00',
                    ],
                    [
                        'jour_semaine' => 'Dimanche',
                        'est_travaille' => true,
                        'est_ferie' => false,
                        'heure_debut' => '10:00',
                        'heure_fin' => '18:00',
                    ],
                ],
                'pauses' => [
                    'Déjeuner' => '45',
                ],
                'configuration' => null,
            ],
        ];
    }

    /**
     * Seed the application's database with example work schedules.
     */
    public function run()
    {
        // Cette méthode n'est pas utilisée directement
        // Nous utilisons plutôt la méthode createForEntreprise
    }

    /**
     * Crée des plages horaires exemples pour une entreprise spécifique
     */
    public static function createForEntreprise(Entreprise $entreprise)
    {
        $plagesHoraires = self::getPlagesHorairesExemples();
        $created = [];
        $existants = 0;

        foreach ($plagesHoraires as $plageHoraire) {
            // Vérifier si une plage horaire similaire existe déjà pour cette entreprise
            $existant = PlageHoraire::where('entreprise_id', $entreprise->id)
                ->where('nom', $plageHoraire['nom'])
                ->first();
            
            // Ne créer que s'il n'existe pas déjà
            if (!$existant) {
                // Convertir les heures en format datetime
                $plageHoraire['heure_debut'] = Carbon::parse($plageHoraire['heure_debut'])->format('H:i:s');
                $plageHoraire['heure_fin'] = Carbon::parse($plageHoraire['heure_fin'])->format('H:i:s');
                
                // Convertir les heures dans les jours de travail
                if (isset($plageHoraire['jours_travail']) && is_array($plageHoraire['jours_travail'])) {
                    foreach ($plageHoraire['jours_travail'] as $key => $jour) {
                        if (isset($jour['heure_debut'])) {
                            $plageHoraire['jours_travail'][$key]['heure_debut'] = Carbon::parse($jour['heure_debut'])->format('H:i:s');
                        }
                        if (isset($jour['heure_fin'])) {
                            $plageHoraire['jours_travail'][$key]['heure_fin'] = Carbon::parse($jour['heure_fin'])->format('H:i:s');
                        }
                    }
                }
                
                $plageHoraire['entreprise_id'] = $entreprise->id;
                $created[] = PlageHoraire::create($plageHoraire);
            } else {
                $existants++;
            }
        }

        return [
            'created' => $created,
            'existants' => $existants,
            'total' => count($plagesHoraires)
        ];
    }
}
