<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use App\Models\Entreprise;
use App\Models\Site;
use App\Models\Departement;
use App\Models\Employeur;
use App\Models\Filiale;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

/**
 * Migre les données réelles de l'entreprise GENIUS GROUPS
 * (info@geniusgroups.ci) depuis le dump elngbpzd_geniuswork.sql.
 *
 * Idempotent : utilise updateOrCreate pour pouvoir être relancé.
 */
class GeniusGroupsMigrationSeeder extends Seeder
{
    private const ENTREPRISE_ID = '019a0b82-4f14-7234-8948-ba609355b410';
    private const FILIALE_ID   = '019a0b88-c31e-7222-b757-7ec3f3993dfb';

    public function run(): void
    {
        // S'assurer que les rôles Spatie existent
        foreach (['super_admin', 'support', 'admin', 'manager', 'rh', 'employee', 'employeur'] as $r) {
            Role::firstOrCreate(['name' => $r, 'guard_name' => 'web']);
        }

        // Permettre de fixer les UUIDs explicitement (mass-assignment)
        Model::unguard();

        DB::beginTransaction();

        try {
            $this->createEntreprise();
            $this->createSites();
            $this->createFiliale();
            $this->createDepartements();
            $this->createEmployeurs();
            $this->createUsers();

            DB::commit();

            Model::reguard();

            $this->printSummary();
        } catch (\Exception $e) {
            DB::rollBack();
            Model::reguard();
            $this->command->error('❌ Erreur : ' . $e->getMessage());
            throw $e;
        }
    }

    // ──────────────────────────────────────────────
    // 1. Entreprise
    // ──────────────────────────────────────────────
    private function createEntreprise(): void
    {
        Entreprise::updateOrCreate(
            ['id' => self::ENTREPRISE_ID],
            [
                'nom'               => 'GENIUS GROUPS',
                'code'              => 'GEN',
                'email'             => 'info@geniusgroups.ci',
                'telephone'          => '0704750465',
                'site_web'          => 'https://geniusgroups.ci',
                'logo'              => 'logos/01KG04A90TSJJVYZF2HPJF9X74.png',
                'devise'            => 'FCFA',
                'fuseau_horaire'    => 'Abidjan',
                'langue'            => 'fr',
                'configuration'     => json_encode(['numero_contribuable' => '2504566S', 'cnps' => null]),
                'statut'            => 'actif',
                'raison_sociale'    => 'GENIUS GROUPS SAS',
                'rccm'              => 'CI-ABJ-03-2025-B13-00081',
                'secteur_activite'  => 'Informatique / Télécoms',
                'nombre_employes'   => 25,
                'adresse'           => "Abidjan - Cocody, Route d'Abatta, Non Loin du Collège Vénézua",
                'ville'             => 'Abidjan',
                'pays'              => "Côte d'Ivoire",
            ]
        );
    }

    // ──────────────────────────────────────────────
    // 2. Sites (actifs uniquement)
    // ──────────────────────────────────────────────
    private function createSites(): void
    {
        $sites = [
            [
                'id'              => '019a0b87-859f-7221-814d-92f825afa950',
                'nom'             => 'Siège Social',
                'adresse'         => 'COCODY, RIVIERA BONOUMIN, CHEVO',
                'code_postal'     => '01 BP 3557',
                'ville'           => 'Abidjan',
                'pays'            => "Côte d'Ivoire",
                'latitude'        => 5.359292,
                'longitude'       => -3.969864,
                'rayon_geofencing'=> 50,
                'has_geofencing'  => true,
                'statut'          => 'actif',
                'description'      => 'Siège social principal de l\'entreprise',
                'horaires'        => json_encode([
                    'Lundi'    => '08:00-12:00, 13:00-17:00',
                    'Mardi'    => '08:00-12:00, 13:00-17:00',
                    'Mercredi' => '08:00-12:00, 13:00-17:00',
                    'Jeudi'    => '08:00-12:00, 13:00-17:00',
                    'Vendredi' => '08:00-12:00, 13:00-17:00',
                ]),
                'contact_nom'      => 'JEREMIE LACINA',
                'contact_email'    => 'jeremie@genius.ci',
                'contact_telephone' => '0705348529',
                'qr_token'         => 'usLqOQNCpp66v01X6C6MLBtL7zuN1kAu',
            ],
            [
                'id'              => '019a0b87-85ac-739e-a457-0528ebb87ab7',
                'nom'             => 'Siège Secondaire',
                'adresse'         => 'Abidjan, COCODY ROUTE  ABATTA PORTE 189',
                'code_postal'     => null,
                'ville'           => 'Abidjan',
                'pays'            => "Côte d'Ivoire",
                'latitude'        => 5.351521,
                'longitude'       => -3.925714,
                'rayon_geofencing'=> 100,
                'has_geofencing'  => true,
                'statut'          => 'actif',
                'description'      => 'Siège social secondaire de l\'entreprise',
                'horaires'        => json_encode([
                    'Lundi'    => ' 08:00-12:00, 13:00-17:00',
                    'Mardi'    => ' 08:00-12:00, 13:00-17:00',
                    'Mercredi' => ' 08:00-12:00, 13:00-17:00',
                    'Jeudi'    => ' 08:00-12:00, 13:00-17:00',
                    'Vendredi' => ' 08:00-12:00, 13:00-17:00',
                ]),
                'contact_nom'      => 'Roxane',
                'contact_email'    => 'roxane@genius.ci',
                'contact_telephone' => '0707918554',
                'qr_token'         => 'gwIYI55gyWdiomlpOvmELv96qNeA3T7m',
            ],
        ];

        foreach ($sites as $s) {
            $site = Site::updateOrCreate(
                ['id' => $s['id']],
                array_merge($s, ['entreprise_id' => self::ENTREPRISE_ID])
            );
            // Générer un kiosk_token si absent
            if (empty($site->kiosk_token)) {
                $site->kiosk_token = Str::random(48);
                $site->save();
            }
        }
    }

    // ──────────────────────────────────────────────
    // 3. Filiale (active uniquement)
    // ──────────────────────────────────────────────
    private function createFiliale(): void
    {
        Filiale::updateOrCreate(
            ['id' => self::FILIALE_ID],
            [
                'entreprise_id' => self::ENTREPRISE_ID,
                'site_id'       => '019a0b87-85ac-739e-a457-0528ebb87ab7',
                'nom'          => 'Filiale Abatta',
                'code'         => 'FILIA_019a0b82',
                'adresse'      => 'Abidjan, COCODY ROUTE  ABATTA PORTE 189',
                'ville'        => 'Abidjan',
                'pays'         => "Côte d'Ivoire",
                'telephone'    => '+225 27 22 252 628',
                'email'        => 'rhum@genius.ci',
                'site_web'     => 'https://genius.ci',
                'description'  => 'Filiale principal de l\'entreprise',
                'statut'       => 'actif',
            ]
        );
    }

    // ──────────────────────────────────────────────
    // 4. Départements (actifs uniquement)
    // ──────────────────────────────────────────────
    private function createDepartements(): void
    {
        $depts = [
            [
                'id'           => '019a0b8a-9a9c-71d8-b3bc-03545868cd36',
                'filiale_id'   => self::FILIALE_ID,
                'parent_id'    => null,
                'nom'          => 'Direction Générale',
                'code'         => 'DIR-GEN_019a0b82',
                'description'  => "Direction et gestion stratégique de l'entreprise",
                'configuration'=> json_encode(['limite_employes' => '10', 'budget' => '0']),
                'niveau'       => 1,
            ],
            [
                'id'           => '019a0b8a-9ab1-7275-b8c6-ca5726a2b415',
                'filiale_id'   => self::FILIALE_ID,
                'parent_id'    => '019a0b8a-9a9c-71d8-b3bc-03545868cd36',
                'nom'          => 'Communication et Vente',
                'code'         => 'CMVT_019a0b82',
                'description'  => "Stratégie marketing, communication et relations publiques\nCommerciale et ventes",
                'configuration'=> json_encode(['limite_employes' => '25', 'budget' => '0']),
                'niveau'       => 2,
            ],
            [
                'id'           => '019a0b8a-9ab6-71d6-9a4e-103588b73dda',
                'filiale_id'   => self::FILIALE_ID,
                'parent_id'    => '019a0b8a-9a9c-71d8-b3bc-03545868cd36',
                'nom'          => "Informatique et Systèmes d'Information",
                'code'         => 'IT_019a0b82',
                'description'  => 'Gestion des infrastructures IT et développement logiciel',
                'configuration'=> json_encode(['limite_employes' => '30', 'budget' => '0']),
                'niveau'       => 2,
            ],
        ];

        foreach ($depts as $d) {
            Departement::updateOrCreate(
                ['id' => $d['id']],
                array_merge($d, [
                    'entreprise_id' => self::ENTREPRISE_ID,
                    'statut'        => 'actif',
                ])
            );
        }
    }

    // ──────────────────────────────────────────────
    // 5. Employeurs (tous, y compris soft-deleted)
    // ──────────────────────────────────────────────
    private function createEmployeurs(): void
    {
        $deptDG   = '019a0b8a-9a9c-71d8-b3bc-03545868cd36';
        $deptCMVT = '019a0b8a-9ab1-7275-b8c6-ca5726a2b415';
        $deptIT   = '019a0b8a-9ab6-71d6-9a4e-103588b73dda';

        $employeurs = [
            [
                'id' => '019a2b99-1551-7283-b919-9e879fbd26ce',
                'code_employe' => 'EMP-GEN-MR250001', 'qr_code_secret' => 'uw9as9E7zaBu3boKxyG7IpDahNa30acU',
                'departement_id' => $deptDG, 'matricule' => 'MAT-GEN-MR001',
                'nom' => 'Roxane', 'prenom' => 'Modjo', 'email' => 'roxane@genius.ci',
                'telephone' => '+225 0707918554', 'date_naissance' => '2001-07-03', 'lieu_naissance' => 'Koumassi',
                'genre' => 'F', 'date_embauche' => '2025-09-01', 'type_contrat' => 'cdi',
                'salaire_base' => 0, 'poste' => 'Chargée des Opérations', 'statut' => 'actif',
                'configuration' => json_encode(['device_model' => 'Xiaomi 2409BRN2CA', 'device_id' => 'AP3A.240905.015.A2']),
                'meta_donnees' => '[]', 'deleted_at' => null,
            ],
            [
                'id' => '019a2b9f-02dd-709b-842d-0eff310ebafa',
                'code_employe' => 'EMP-GEN-NF250001', 'qr_code_secret' => 'RhH3BV2KFzJcbDcMfupDcA1AyddL8jKX',
                'departement_id' => $deptCMVT, 'matricule' => 'MAT-GEN-NF001',
                'nom' => 'Fidel', 'prenom' => "N'zoué", 'email' => 'fidel@genius.ci',
                'telephone' => '+225 0787658125', 'date_naissance' => '2003-04-24', 'lieu_naissance' => 'Yamoussoukro',
                'genre' => 'M', 'date_embauche' => '2025-09-01', 'type_contrat' => 'cdi',
                'salaire_base' => 0, 'poste' => 'CPO (Chief Product Officer)', 'statut' => 'actif',
                'configuration' => json_encode(['device_model' => 'samsung SM-A065F', 'device_id' => 'UP1A.231005.007']),
                'meta_donnees' => '[]', 'deleted_at' => null,
            ],
            [
                'id' => '019a2ba3-0c2b-7218-87d5-01ebb6d5b62e',
                'code_employe' => 'EMP-GEN-BM250001', 'qr_code_secret' => '0JTA6v8caVWl6Zj0p9kOHvkxnjxAjoFx',
                'departement_id' => $deptCMVT, 'matricule' => 'MAT-GEN-BM001',
                'nom' => 'Mbene', 'prenom' => 'Bamba', 'email' => 'mbene@genius.ci',
                'telephone' => '+225 0749516990', 'date_naissance' => '2002-05-15', 'lieu_naissance' => 'ADJAMÉ ',
                'genre' => 'F', 'date_embauche' => '2025-09-01', 'type_contrat' => 'cdi',
                'salaire_base' => 0, 'poste' => 'CCO (Chief Customer Officer)', 'statut' => 'actif',
                'configuration' => '[]', 'meta_donnees' => '[]', 'deleted_at' => null,
            ],
            [
                'id' => '019a2ba5-8eb2-733b-91b2-a170c7107478',
                'code_employe' => 'EMP-GEN-LT250001', 'qr_code_secret' => 'waGnIAc7XrykWzOCmnkJ2dWnJdOZ8B54',
                'departement_id' => $deptCMVT, 'matricule' => 'MAT-GEN-LT001',
                'nom' => 'Lacina', 'prenom' => 'Timothée ', 'email' => 'timothee@genius.ci',
                'telephone' => '+225 01 01 28 84 56', 'date_naissance' => '2004-03-08', 'lieu_naissance' => 'Assinie France',
                'genre' => 'M', 'date_embauche' => '2025-09-01', 'type_contrat' => 'cdi',
                'salaire_base' => 0, 'poste' => 'Digital Manager', 'statut' => 'actif',
                'configuration' => '[]', 'meta_donnees' => '[]', 'deleted_at' => null,
            ],
            [
                'id' => '019a2ba8-2f99-73c5-86c7-5432e4f4387d',
                'code_employe' => 'EMP-GEN-YE250001', 'qr_code_secret' => 'rwoGAcz30N7ZINoley9QKAWFuilYDJdI',
                'departement_id' => $deptIT, 'matricule' => 'MAT-GEN-YE001',
                'nom' => 'Yao', 'prenom' => 'Emmanuel', 'email' => 'yao@genius.ci',
                'telephone' => '+225 05 56 98 50 04', 'date_naissance' => '2003-06-16', 'lieu_naissance' => 'ZAKOEOUA',
                'genre' => 'M', 'date_embauche' => '2025-09-02', 'type_contrat' => 'cdi',
                'salaire_base' => 0, 'poste' => 'CTO (Chief Technology Officer)', 'statut' => 'actif',
                'configuration' => '[]', 'meta_donnees' => '[]', 'deleted_at' => null,
            ],
            [
                'id' => '019a2bac-4448-70e1-b059-71bab083739b',
                'code_employe' => 'EMP-GEN-KN250001', 'qr_code_secret' => 'ziMDaw1EK137lvzy4zSDmwGKaxA47cId',
                'departement_id' => $deptIT, 'matricule' => 'MAT-GEN-KN001',
                'nom' => 'Kié', 'prenom' => 'Noël ', 'email' => 'noel@genius.ci',
                'telephone' => '+225 01 01 75 28 86', 'date_naissance' => '2004-08-06', 'lieu_naissance' => 'ZUENOULA COMMUNE',
                'genre' => 'M', 'date_embauche' => '2025-09-02', 'type_contrat' => 'cdi',
                'salaire_base' => 50000, 'poste' => 'Développeur Informatique Back-end', 'statut' => 'inactif',
                'configuration' => '[]', 'meta_donnees' => '[]', 'deleted_at' => '2026-04-25 18:18:00',
            ],
            [
                'id' => '019a2bb2-469d-7014-a727-8e4ca8d92748',
                'code_employe' => 'EMP-GEN-SM250001', 'qr_code_secret' => '6rhtJ9OG1JZnZ0iJIOMrYKLWP2ofTq62',
                'departement_id' => $deptIT, 'matricule' => 'MAT-GEN-SM001',
                'nom' => 'Sialou', 'prenom' => 'Michael', 'email' => 'michael@genius.ci',
                'telephone' => '+225 07 09 59 79 04', 'date_naissance' => '1995-04-12', 'lieu_naissance' => 'BOUAKÉ COMMUNE',
                'genre' => 'M', 'date_embauche' => '2025-10-16', 'type_contrat' => 'stage',
                'salaire_base' => 0, 'poste' => 'Développeur Informatique Front-end', 'statut' => 'actif',
                'configuration' => '[]', 'meta_donnees' => '[]', 'deleted_at' => null,
            ],
            [
                'id' => '019a2bb5-aa7c-7157-9218-257a180f381f',
                'code_employe' => 'EMP-GEN-KJ250001', 'qr_code_secret' => '5yil1Xzi89yAxMPXjHHJo65O2AFBEKJB',
                'departement_id' => $deptIT, 'matricule' => 'MAT-GEN-KJ001',
                'nom' => 'Kassi', 'prenom' => 'Joseph ', 'email' => 'kassi@genius.ci',
                'telephone' => '+225 07 00 69 53 27', 'date_naissance' => '2002-12-13', 'lieu_naissance' => "ABEVE/N'DOUCI",
                'genre' => null, 'date_embauche' => '2025-09-24', 'type_contrat' => 'stage',
                'salaire_base' => 0, 'poste' => 'Développeur Informatique Back-end', 'statut' => 'actif',
                'configuration' => '[]', 'meta_donnees' => '[]', 'deleted_at' => null,
            ],
            [
                'id' => '019a2bc6-be22-7191-a9b2-cd0e39209d98',
                'code_employe' => 'EMP-GEN-YS250001', 'qr_code_secret' => 'JT3IFF0UVYskiSJ4CnCYiSV9SOpK8TiR',
                'departement_id' => $deptIT, 'matricule' => 'MAT-GEN-YS001',
                'nom' => 'Yapi', 'prenom' => 'Samuel', 'email' => 'samuel@genius.ci',
                'telephone' => '+225 01 52 03 20 65', 'date_naissance' => '2005-02-05', 'lieu_naissance' => 'Yopougon',
                'genre' => 'M', 'date_embauche' => '2025-10-28', 'type_contrat' => 'stage',
                'salaire_base' => 0, 'poste' => 'Développeur Informatique Front-end', 'statut' => 'actif',
                'configuration' => '[]', 'meta_donnees' => '[]', 'deleted_at' => null,
            ],
            [
                'id' => '019a2bca-0a81-7236-81ca-f1597f072be6',
                'code_employe' => 'EMP-GEN-DA250001', 'qr_code_secret' => 'qOOmS4yNN0wqsSQo6ZpCWIMR1gPtjrHg',
                'departement_id' => $deptIT, 'matricule' => 'MAT-GEN-DA001',
                'nom' => 'Dolo', 'prenom' => 'Aissa', 'email' => 'aissa@genius.ci',
                'telephone' => '+225 07 08 23 00 88', 'date_naissance' => '1996-04-18', 'lieu_naissance' => 'Attécoubé ',
                'genre' => 'F', 'date_embauche' => '2025-10-28', 'type_contrat' => 'stage',
                'salaire_base' => 0, 'poste' => 'Développeur Informatique Back-office', 'statut' => 'actif',
                'configuration' => '[]', 'meta_donnees' => '[]', 'deleted_at' => '2025-11-11 07:45:31',
            ],
            [
                'id' => '019a2bd6-3331-71e7-a47f-dd8e4d672c8f',
                'code_employe' => 'EMP-GEN-BV250001', 'qr_code_secret' => 'EKpKDFQjWLKzAMojjUD5aF0lERI6EELk',
                'departement_id' => $deptIT, 'matricule' => 'MAT-GEN-BV001',
                'nom' => 'Bamba', 'prenom' => 'Victoire', 'email' => 'victoire@genius.ci',
                'telephone' => '+2250170023468', 'date_naissance' => '2000-08-20', 'lieu_naissance' => 'Aboco',
                'genre' => 'F', 'date_embauche' => '2025-10-28', 'type_contrat' => 'stage',
                'salaire_base' => 0, 'poste' => 'Développeur Informatique Front-end', 'statut' => 'inactif',
                'configuration' => json_encode(['device_model' => 'samsung SM-A165F', 'device_id' => 'AP3A.240905.015.A2']),
                'meta_donnees' => '[]', 'deleted_at' => '2026-04-25 18:18:00',
            ],
            [
                'id' => '019a2bda-6a9e-73ad-b3ec-a57da952d10d',
                'code_employe' => 'EMP-GEN-NJ250001', 'qr_code_secret' => 'zfJW9z8zHBJ0TJP6dUiaTgMe7XG6mqaa',
                'departement_id' => $deptDG, 'matricule' => 'MAT-GEN-NJ001',
                'nom' => "N'da", 'prenom' => 'Jérémie', 'email' => 'jeremie@genius.ci',
                'telephone' => '+225 07 05 34 85 29', 'date_naissance' => '2002-07-20', 'lieu_naissance' => 'Assinie mafia',
                'genre' => 'M', 'date_embauche' => '2025-07-28', 'type_contrat' => 'cdi',
                'salaire_base' => 0, 'poste' => 'CEO et CTO', 'statut' => 'actif',
                'configuration' => '[]', 'meta_donnees' => '[]', 'deleted_at' => null,
            ],
        ];

        foreach ($employeurs as $e) {
            $deletedAt = $e['deleted_at'];
            unset($e['deleted_at']);

            $data = array_merge($e, [
                'entreprise_id'  => self::ENTREPRISE_ID,
                'filiale_id'     => self::FILIALE_ID,
                'qr_code_active' => 1,
                'created_at'    => now(),
                'updated_at'    => now(),
            ]);

            // Utiliser DB::table pour contourner les événements du modèle
            // qui régénèrent code_employe, matricule et qr_code_secret
            $existing = DB::table('employeurs')->where('id', $e['id'])->first();
            if ($existing) {
                DB::table('employeurs')->where('id', $e['id'])->update($data);
            } else {
                DB::table('employeurs')->insert($data);
            }

            // Gérer le soft-delete
            if ($deletedAt) {
                DB::table('employeurs')->where('id', $e['id'])->update(['deleted_at' => $deletedAt]);
            }
        }
    }

    // ──────────────────────────────────────────────
    // 6. Utilisateurs
    // ──────────────────────────────────────────────
    private function createUsers(): void
    {
        // Mot de passe par défaut pour tous (à changer en production)
        $defaultPassword = '$2y$12$iHSBlA860wnr6cVl8MU7eugBHoy53/vOb0Xuac/8MFHKl./2drjoe';

        $users = [
            [
                'id' => '019a0b7e-ec35-7117-8d81-74aed7269461',
                'name' => 'Roxane Modjo', 'email' => 'kouassinellyroxane@gmail.com',
                'phone' => '0707918554', 'telephone' => '0707918554',
                'role' => 'admin', 'statut' => 'actif',
                'entreprise_id' => self::ENTREPRISE_ID, 'employeur_id' => null,
                'spatie_role' => 'admin',
                'settings' => json_encode(['notifications_email' => true, 'notifications_app' => true]),
                'preferences' => json_encode(['theme' => 'light', 'sidebar_collapsed' => false]),
            ],
            [
                'id' => '019a2bda-6a9e-73ad-b3ec-a57da952d10d',
                'name' => "Jérémie N'da", 'email' => 'jeremie@genius.ci',
                'phone' => null, 'telephone' => '0705348529',
                'role' => 'manager', 'statut' => 'actif',
                'entreprise_id' => self::ENTREPRISE_ID, 'employeur_id' => null,
                'spatie_role' => null,
                'settings' => null, 'preferences' => null,
            ],
            [
                'id' => '019a2bdd-c516-7096-94ce-d56b1cb2de32',
                'name' => 'Modjo Roxane', 'email' => 'roxane@genius.ci',
                'phone' => null, 'telephone' => null,
                'role' => 'employeur', 'statut' => 'actif',
                'entreprise_id' => self::ENTREPRISE_ID, 'employeur_id' => '019a2b99-1551-7283-b919-9e879fbd26ce',
                'spatie_role' => 'employeur',
                'settings' => null, 'preferences' => null,
            ],
            [
                'id' => '019a6e44-5787-7178-907d-0c78b946a954',
                'name' => "N'zoué Fidel", 'email' => 'fidel@genius.ci',
                'phone' => null, 'telephone' => null,
                'role' => 'employeur', 'statut' => 'actif',
                'entreprise_id' => self::ENTREPRISE_ID, 'employeur_id' => '019a2b9f-02dd-709b-842d-0eff310ebafa',
                'spatie_role' => 'employeur',
                'settings' => null, 'preferences' => null,
            ],
            [
                'id' => '019a90e5-3fc9-72b2-ac7e-2a6f0e8cb76e',
                'name' => 'Bamba Victoire', 'email' => 'victoire@genius.ci',
                'phone' => null, 'telephone' => null,
                'role' => 'employeur', 'statut' => 'actif',
                'entreprise_id' => self::ENTREPRISE_ID, 'employeur_id' => '019a2bd6-3331-71e7-a47f-dd8e4d672c8f',
                'spatie_role' => 'employeur',
                'settings' => null, 'preferences' => null,
            ],
        ];

        foreach ($users as $u) {
            $spatieRole = $u['spatie_role'];
            unset($u['spatie_role']);

            $user = User::updateOrCreate(
                ['id' => $u['id']],
                array_merge($u, [
                    'password'            => $defaultPassword,
                    'langue'              => 'fr',
                    'fuseau_horaire'      => 'Africa/Abidjan',
                    'two_factor_enabled'  => 1,
                    'two_factor_verified' => 1,
                ])
            );

            // Assigner le rôle Spatie
            if ($spatieRole) {
                $user->syncRoles([$spatieRole]);
            }
        }
    }

    // ──────────────────────────────────────────────
    // Résumé
    // ──────────────────────────────────────────────
    private function printSummary(): void
    {
        $entreprise = Entreprise::find(self::ENTREPRISE_ID);
        $sites = Site::where('entreprise_id', self::ENTREPRISE_ID)->get();
        $employeurs = Employeur::withTrashed()->where('entreprise_id', self::ENTREPRISE_ID)->get();

        $this->command->info('');
        $this->command->info('✅ Migration GENIUS GROUPS terminée !');
        $this->command->info('');
        $this->command->info('🏢 Entreprise : ' . $entreprise->nom . ' (' . $entreprise->email . ')');
        $this->command->info('');
        $this->command->info('📍 Sites :');
        foreach ($sites as $site) {
            $this->command->info('   • ' . $site->nom . ' — kiosk: ' . url('/kiosk/' . $site->kiosk_token));
        }
        $this->command->info('');
        $this->command->info('👥 Employés (' . $employeurs->count() . ') :');
        foreach ($employeurs as $emp) {
            $status = $emp->trashed() ? ' [supprimé]' : '';
            $this->command->info('   • ' . $emp->prenom . ' ' . $emp->nom . ' — ' . $emp->poste . $status);
            $this->command->info('     QR : ' . $emp->qr_code_secret);
        }
        $this->command->info('');
        $this->command->info('🔑 URL Kiosque (Siège Social) :');
        $site = $sites->firstWhere('nom', 'Siège Social');
        if ($site) {
            $this->command->info('   ' . url('/kiosk/' . $site->kiosk_token));
        }
        $this->command->info('');
        $this->command->warn('⚠️  Mot de passe par défaut pour tous les utilisateurs : celui de la base source.');
        $this->command->warn('   Changez-le immédiatement en production.');
    }
}
