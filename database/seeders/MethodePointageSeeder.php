<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MethodePointage;
use App\Models\Entreprise;

class MethodePointageSeeder extends Seeder
{
    public function run()
    {
        $entreprises = Entreprise::all();

        foreach ($entreprises as $entreprise) {
            $methodesPointage = [
                [
                    'entreprise_id' => $entreprise->id,
                    'nom' => 'QR Code',
                    'code' => 'QR-' . $entreprise->id,
                    'description' => 'Pointage via scan de QR Code',
                    'necessite_photo' => false,
                    'necessite_geolocalisation' => true,
                    'necessite_signature' => false,
                    'necessite_validation' => false,
                    'autoriser_hors_site' => false,
                    'rayon_geofencing' => 100,
                    'configuration' => json_encode([
                        'duree_validite_qr' => 30,
                        'rotation_automatique' => true,
                        'interval_rotation' => 60
                    ]),
                    'validation_regles' => json_encode([
                        'verifier_appareil' => true,
                        'verifier_ip' => true
                    ])
                ],
                [
                    'entreprise_id' => $entreprise->id,
                    'nom' => 'Géolocalisation',
                    'code' => 'GEO-' . $entreprise->id,
                    'description' => 'Pointage par géolocalisation',
                    'necessite_photo' => true,
                    'necessite_geolocalisation' => true,
                    'necessite_signature' => false,
                    'necessite_validation' => true,
                    'autoriser_hors_site' => false,
                    'rayon_geofencing' => 50,
                    'configuration' => json_encode([
                        'precision_requise' => 20,
                        'delai_validation' => 5,
                        'photo_selfie' => true
                    ]),
                    'validation_regles' => json_encode([
                        'verifier_appareil' => true,
                        'verifier_ip' => true,
                        'verifier_precision' => true
                    ])
                ],
                [
                    'entreprise_id' => $entreprise->id,
                    'nom' => 'Badge NFC',
                    'code' => 'NFC-' . $entreprise->id,
                    'description' => 'Pointage via badge NFC',
                    'necessite_photo' => false,
                    'necessite_geolocalisation' => true,
                    'necessite_signature' => false,
                    'necessite_validation' => true,
                    'autoriser_hors_site' => false,
                    'rayon_geofencing' => 20,
                    'configuration' => json_encode([
                        'verification_uid' => true,
                        'rotation_cle' => true,
                        'interval_rotation' => 24
                    ]),
                    'validation_regles' => json_encode([
                        'verifier_appareil' => true,
                        'verifier_badge' => true
                    ])
                ]
            ];

            foreach ($methodesPointage as $methode) {
                MethodePointage::create($methode);
            }
        }
    }
}
