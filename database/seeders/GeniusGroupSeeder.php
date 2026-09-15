<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Entreprise;
use App\Models\Site;
use App\Models\Departement;
use App\Models\Employeur;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class GeniusGroupSeeder extends Seeder
{
    public function run(): void
    {
        // S'assurer que les rôles existent
        $roles = ['super_admin', 'admin', 'support', 'employe', 'manager'];
        foreach ($roles as $roleName) {
            Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
        }

        DB::beginTransaction();

        try {
            // ===========================================
            // 1. Entreprise GeniusGroup
            // ===========================================
            $entreprise = Entreprise::create([
                'nom' => 'GeniusGroup',
                'code' => 'GG',
                'description' => 'GeniusGroup — Solutions de paiement et gestion d\'entreprise',
                'email' => 'contact@geniusgroup.ci',
                'telephone' => '+22527222628',
                'site_web' => 'https://geniuspay.tech',
                'secteur_activite' => 'Technologies',
                'raison_sociale' => 'GeniusGroup SARL',
                'devise' => 'XOF',
                'fuseau_horaire' => 'Africa/Abidjan',
                'langue' => 'fr',
                'statut' => 'actif',
                'nombre_employes' => 25,
                'configuration' => json_encode([
                    'fuseau_horaire' => 'Africa/Abidjan',
                    'devise' => 'XOF',
                    'langue' => 'fr',
                    'jours_travail' => ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi'],
                ]),
            ]);

            // ===========================================
            // 2. Départements
            // ===========================================
            $departements = [
                ['nom' => 'Direction Générale', 'code' => 'GG-DG', 'description' => 'Direction et administration'],
                ['nom' => 'Ressources Humaines', 'code' => 'GG-RH', 'description' => 'Gestion des ressources humaines'],
                ['nom' => 'Technologies', 'code' => 'GG-TECH', 'description' => 'Développement et IT'],
                ['nom' => 'Finance et Comptabilité', 'code' => 'GG-FIN', 'description' => 'Gestion financière'],
            ];

            foreach ($departements as $dept) {
                Departement::create([
                    'entreprise_id' => $entreprise->id,
                    'nom' => $dept['nom'],
                    'code' => $dept['code'],
                    'description' => $dept['description'],
                    'statut' => 'actif',
                ]);
            }

            // ===========================================
            // 3. Site — Siège Social avec kiosk_token
            // ===========================================
            $site = new Site([
                'entreprise_id' => $entreprise->id,
                'nom' => 'Siège Social',
                'adresse' => 'Cocody, Abidjan',
                'code_postal' => '00225',
                'ville' => 'Abidjan',
                'pays' => 'Côte d\'Ivoire',
                'latitude' => 5.3600,
                'longitude' => -4.0083,
                'rayon_geofencing' => 100,
                'has_geofencing' => false,
                'statut' => 'actif',
                'description' => 'Siège social de GeniusGroup',
                'horaires' => json_encode([
                    'lundi' => ['08:00-12:00', '13:00-17:00'],
                    'mardi' => ['08:00-12:00', '13:00-17:00'],
                    'mercredi' => ['08:00-12:00', '13:00-17:00'],
                    'jeudi' => ['08:00-12:00', '13:00-17:00'],
                    'vendredi' => ['08:00-12:00', '13:00-17:00'],
                    'samedi' => [],
                    'dimanche' => [],
                ]),
                'contact_nom' => 'Responsable Site',
                'contact_email' => 'site@geniusgroup.ci',
                'contact_telephone' => '+22527222628',
            ]);
            $site->kiosk_token = Str::random(48);
            $site->save();

            // ===========================================
            // 4. Admin GeniusGroup
            // ===========================================
            $admin = User::create([
                'name' => 'Admin GeniusGroup',
                'email' => 'admin@geniusgroup.ci',
                'phone' => '+2250704750465',
                'password' => Hash::make('GeniusGroup2025!'),
                'email_verified_at' => now(),
                'phone_verified_at' => now(),
                'pin' => Hash::make('1234'),
                'pin_attempts' => 0,
                'pin_changed_at' => now(),
                'statut' => 'actif',
                'role' => 'super_admin',
                'preferences' => [
                    'langue' => 'fr',
                    'fuseau_horaire' => 'Africa/Abidjan',
                    'notifications' => ['email' => true, 'sms' => true, 'push' => true],
                ],
            ]);
            $admin->assignRole('super_admin');

            // ===========================================
            // 5. Employés avec QR codes
            // ===========================================
            $deptDG = Departement::where('entreprise_id', $entreprise->id)->where('nom', 'Direction Générale')->first();
            $deptRH = Departement::where('entreprise_id', $entreprise->id)->where('nom', 'Ressources Humaines')->first();
            $deptTech = Departement::where('entreprise_id', $entreprise->id)->where('nom', 'Technologies')->first();

            $employes = [
                [
                    'departement_id' => $deptDG->id,
                    'nom' => 'Kouassi',
                    'prenom' => 'Jean',
                    'email' => 'jean.kouassi@geniusgroup.ci',
                    'telephone' => '+225070000001',
                    'poste' => 'Directeur Général',
                    'genre' => 'M',
                ],
                [
                    'departement_id' => $deptRH->id,
                    'nom' => 'Traoré',
                    'prenom' => 'Aminata',
                    'email' => 'aminata.traore@geniusgroup.ci',
                    'telephone' => '+225070000002',
                    'poste' => 'Responsable RH',
                    'genre' => 'F',
                ],
                [
                    'departement_id' => $deptTech->id,
                    'nom' => 'Bamba',
                    'prenom' => 'Ibrahim',
                    'email' => 'ibrahim.bamba@geniusgroup.ci',
                    'telephone' => '+225070000003',
                    'poste' => 'Développeur',
                    'genre' => 'M',
                ],
                [
                    'departement_id' => $deptTech->id,
                    'nom' => 'Diallo',
                    'prenom' => 'Fatou',
                    'email' => 'fatou.diallo@geniusgroup.ci',
                    'telephone' => '+225070000004',
                    'poste' => 'Chef de Projet',
                    'genre' => 'F',
                ],
            ];

            foreach ($employes as $emp) {
                $employeur = new Employeur([
                    'entreprise_id' => $entreprise->id,
                    'departement_id' => $emp['departement_id'],
                    'matricule' => 'GG-' . strtoupper(Str::random(6)),
                    'nom' => $emp['nom'],
                    'prenom' => $emp['prenom'],
                    'email' => $emp['email'],
                    'telephone' => $emp['telephone'],
                    'photo' => null,
                    'date_naissance' => '1990-01-01',
                    'lieu_naissance' => 'Abidjan',
                    'genre' => $emp['genre'],
                    'nationalite' => 'Ivoirienne',
                    'type_piece' => 'CNI',
                    'numero_piece' => 'C' . rand(100000000, 999999999),
                    'date_embauche' => now(),
                    'type_contrat' => 'CDI',
                    'poste' => $emp['poste'],
                    'salaire_base' => 500000,
                    'statut' => 'actif',
                    'meta_donnees' => json_encode([
                        'situation_matrimoniale' => 'célibataire',
                        'contact_urgence' => '+225070000000',
                        'nom_contact_urgence' => 'Contact Urgence',
                    ]),
                    'configuration' => json_encode([
                        'horaire_travail' => [
                            'debut' => '08:00',
                            'fin' => '17:00',
                            'pause_debut' => '12:00',
                            'pause_fin' => '13:00',
                        ],
                        'notifications' => ['email' => true, 'sms' => true],
                    ]),
                ]);
                $employeur->save();

                // Générer le QR code secret
                $employeur->qr_code_secret = Str::random(32);
                $employeur->save();

                // Compte utilisateur pour l'employé
                User::create([
                    'name' => $employeur->prenom . ' ' . $employeur->nom,
                    'email' => $employeur->email,
                    'phone' => $employeur->telephone,
                    'password' => Hash::make('password'),
                    'pin' => Hash::make('1234'),
                    'employeur_id' => $employeur->id,
                    'statut' => 'actif',
                    'email_verified_at' => now(),
                    'phone_verified_at' => now(),
                    'pin_changed_at' => now(),
                    'preferences' => json_encode([
                        'langue' => 'fr',
                        'fuseau_horaire' => 'Africa/Abidjan',
                        'notifications' => ['email' => true, 'sms' => true],
                    ]),
                ]);
            }

            DB::commit();

            // Afficher les informations importantes
            $this->command->info('✅ GeniusGroup créé avec succès !');
            $this->command->info('');
            $this->command->info('📋 Informations de connexion :');
            $this->command->info('   Admin : admin@geniusgroup.ci / GeniusGroup2025!');
            $this->command->info('');
            $this->command->info('🔑 URL Kiosque :');
            $this->command->info('   ' . url('/kiosk/' . $site->kiosk_token));
            $this->command->info('');
            $this->command->info('👥 Employés créés avec QR codes :');
            foreach (Employeur::where('entreprise_id', $entreprise->id)->get() as $emp) {
                $this->command->info('   ' . $emp->prenom . ' ' . $emp->nom . ' — ' . $emp->poste);
                $this->command->info('      QR secret : ' . $emp->qr_code_secret);
            }

        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error('❌ Erreur : ' . $e->getMessage());
            throw $e;
        }
    }
}
