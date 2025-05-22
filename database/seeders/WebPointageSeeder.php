<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MethodePointage;
use App\Models\Entreprise;

class WebPointageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $entreprises = Entreprise::all();

        foreach ($entreprises as $entreprise) {
            // Ajouter la méthode WebPointage
            MethodePointage::create([
                'entreprise_id' => $entreprise->id,
                'nom' => 'WebPointage',
                'code' => 'WEB-' . $entreprise->id,
                'description' => 'Pointage via WebAuthn et QR Code',
                'necessite_photo' => false,
                'necessite_geolocalisation' => true,
                'necessite_signature' => false,
                'necessite_validation' => false,
                'autoriser_hors_site' => false,
                'rayon_geofencing' => 100,
                'configuration' => json_encode([
                    'duree_validite_qr' => 24, // en heures
                    'rotation_automatique' => true,
                    'interval_rotation' => 24, // en heures
                    'webauthn_required' => true,
                    'allow_multiple_devices' => true,
                    'max_devices_per_user' => 3
                ]),
                'validation_regles' => json_encode([
                    'verifier_appareil' => true,
                    'verifier_ip' => true,
                    'verifier_webauthn' => true
                ]),
                'statut' => 'actif'
            ]);
        }
    }
}
