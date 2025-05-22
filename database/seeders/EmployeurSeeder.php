<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Employeur;
use App\Models\User;
use App\Models\Entreprise;
use App\Models\Departement;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class EmployeurSeeder extends Seeder
{
    public function run(): void
    {
        DB::beginTransaction();

        try {
            $entreprise = Entreprise::first();
            
            if (!$entreprise) {
                throw new \RuntimeException('Aucune entreprise trouvée. Veuillez exécuter EntrepriseSeeder d\'abord.');
            }

            // Récupérer le département Direction Générale existant
            $departement = Departement::where('nom', 'Direction Générale')
                                    ->where('entreprise_id', $entreprise->id)
                                    ->first();

            if (!$departement) {
                throw new \RuntimeException('Département Direction Générale non trouvé. Veuillez exécuter DepartementSeeder d\'abord.');
            }

            // Générer une matricule unique
            $matricule = 'MAT-' . strtoupper(Str::random(8));

            // Créer le manager principal avec des valeurs par défaut pour les champs obligatoires
            $employeur = new Employeur([
                'entreprise_id' => $entreprise->id,
                'departement_id' => $departement->id,
                'matricule' => $matricule,
                'nom' => 'Manager',
                'prenom' => 'Principal',
                'email' => 'manager.principal@techcorp.com',
                'telephone' => '+22503456789',
                'photo' => null,
                'date_naissance' => '1980-01-01',
                'lieu_naissance' => 'Abidjan',
                'genre' => 'M',
                'nationalite' => 'Ivoirienne',
                'type_piece' => 'CNI',
                'numero_piece' => 'C0123456789',
                'date_embauche' => now(),
                'type_contrat' => 'CDI',
                'poste' => 'Directeur Général',
                'salaire_base' => 1000000,
                'statut' => 'actif',
                'meta_donnees' => json_encode([
                    'situation_matrimoniale' => 'marié(e)',
                    'contact_urgence' => '+22507654321',
                    'nom_contact_urgence' => 'Contact Urgence'
                ]),
                'configuration' => json_encode([
                    'horaire_travail' => [
                        'debut' => '08:00',
                        'fin' => '17:00',
                        'pause_debut' => '12:00',
                        'pause_fin' => '13:00'
                    ],
                    'notifications' => [
                        'email' => true,
                        'sms' => true
                    ]
                ]),
                'competences' => json_encode([
                    'Management',
                    'Leadership',
                    'Stratégie d\'entreprise'
                ])
            ]);

            // Générer et sauvegarder l'employeur
            $employeur->save();

            // Créer un compte utilisateur pour l'employeur
            $user = User::create([
                'name' => $employeur->prenom . ' ' . $employeur->nom,
                'email' => $employeur->email,
                'telephone' => $employeur->telephone,
                'password' => Hash::make('password'),
                'pin' => Hash::make('1234'),
                'employeur_id' => $employeur->id,
                'statut' => 'actif',
                'email_verified_at' => now(),
                'telephone_verified_at' => now(),
                'pin_changed_at' => now(),
                'preferences' => json_encode([
                    'langue' => 'fr',
                    'fuseau_horaire' => 'Africa/Abidjan',
                    'notifications' => [
                        'email' => true,
                        'sms' => true
                    ]
                ])
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
