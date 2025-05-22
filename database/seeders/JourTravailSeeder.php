<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\JourTravail;
use App\Models\Entreprise;

class JourTravailSeeder extends Seeder
{
    public function run()
    {
        $entreprises = Entreprise::all();

        foreach ($entreprises as $entreprise) {
            $joursTravail = [
                [
                    'entreprise_id' => $entreprise->id,
                    'jour_semaine' => 'Lundi',
                    'est_travaille' => true,
                    'est_ferie' => false,
                    'heure_debut_standard' => '08:00:00',
                    'heure_fin_standard' => '17:00:00',
                    'duree_pause_standard' => 60,
                    'plages_horaires' => json_encode([
                        [
                            'debut' => '08:00:00',
                            'fin' => '12:00:00'
                        ],
                        [
                            'debut' => '13:00:00',
                            'fin' => '17:00:00'
                        ]
                    ]),
                    'configuration' => json_encode([
                        'flexible_debut' => true,
                        'flexible_fin' => true,
                        'marge_debut' => 30,
                        'marge_fin' => 30
                    ])
                ],
                [
                    'entreprise_id' => $entreprise->id,
                    'jour_semaine' => 'Mardi',
                    'est_travaille' => true,
                    'est_ferie' => false,
                    'heure_debut_standard' => '08:00:00',
                    'heure_fin_standard' => '17:00:00',
                    'duree_pause_standard' => 60,
                    'plages_horaires' => json_encode([
                        [
                            'debut' => '08:00:00',
                            'fin' => '12:00:00'
                        ],
                        [
                            'debut' => '13:00:00',
                            'fin' => '17:00:00'
                        ]
                    ]),
                    'configuration' => json_encode([
                        'flexible_debut' => true,
                        'flexible_fin' => true,
                        'marge_debut' => 30,
                        'marge_fin' => 30
                    ])
                ],
                [
                    'entreprise_id' => $entreprise->id,
                    'jour_semaine' => 'Mercredi',
                    'est_travaille' => true,
                    'est_ferie' => false,
                    'heure_debut_standard' => '08:00:00',
                    'heure_fin_standard' => '17:00:00',
                    'duree_pause_standard' => 60,
                    'plages_horaires' => json_encode([
                        [
                            'debut' => '08:00:00',
                            'fin' => '12:00:00'
                        ],
                        [
                            'debut' => '13:00:00',
                            'fin' => '17:00:00'
                        ]
                    ]),
                    'configuration' => json_encode([
                        'flexible_debut' => true,
                        'flexible_fin' => true,
                        'marge_debut' => 30,
                        'marge_fin' => 30
                    ])
                ],
                [
                    'entreprise_id' => $entreprise->id,
                    'jour_semaine' => 'Jeudi',
                    'est_travaille' => true,
                    'est_ferie' => false,
                    'heure_debut_standard' => '08:00:00',
                    'heure_fin_standard' => '17:00:00',
                    'duree_pause_standard' => 60,
                    'plages_horaires' => json_encode([
                        [
                            'debut' => '08:00:00',
                            'fin' => '12:00:00'
                        ],
                        [
                            'debut' => '13:00:00',
                            'fin' => '17:00:00'
                        ]
                    ]),
                    'configuration' => json_encode([
                        'flexible_debut' => true,
                        'flexible_fin' => true,
                        'marge_debut' => 30,
                        'marge_fin' => 30
                    ])
                ],
                [
                    'entreprise_id' => $entreprise->id,
                    'jour_semaine' => 'Vendredi',
                    'est_travaille' => true,
                    'est_ferie' => false,
                    'heure_debut_standard' => '08:00:00',
                    'heure_fin_standard' => '16:00:00',
                    'duree_pause_standard' => 60,
                    'plages_horaires' => json_encode([
                        [
                            'debut' => '08:00:00',
                            'fin' => '12:00:00'
                        ],
                        [
                            'debut' => '13:00:00',
                            'fin' => '16:00:00'
                        ]
                    ]),
                    'configuration' => json_encode([
                        'flexible_debut' => true,
                        'flexible_fin' => true,
                        'marge_debut' => 30,
                        'marge_fin' => 30
                    ])
                ],
                [
                    'entreprise_id' => $entreprise->id,
                    'jour_semaine' => 'Samedi',
                    'est_travaille' => false,
                    'est_ferie' => false,
                    'heure_debut_standard' => null,
                    'heure_fin_standard' => null,
                    'duree_pause_standard' => null,
                    'plages_horaires' => null,
                    'configuration' => null
                ],
                [
                    'entreprise_id' => $entreprise->id,
                    'jour_semaine' => 'Dimanche',
                    'est_travaille' => false,
                    'est_ferie' => true,
                    'heure_debut_standard' => null,
                    'heure_fin_standard' => null,
                    'duree_pause_standard' => null,
                    'plages_horaires' => null,
                    'configuration' => null
                ]
            ];

            foreach ($joursTravail as $jour) {
                JourTravail::create($jour);
            }
        }
    }
}
