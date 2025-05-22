<?php

namespace App\Services;

use App\Models\Abonnement;
use App\Models\CodePromo;
use App\Models\Employeur;
use App\Models\Entreprise;
use App\Models\PlanAbonnement;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Arr;

class EntrepriseService
{
    public function createEntreprise(array $data): Entreprise
    {
        $startTime = microtime(true);
        Log::channel('queries')->info('Début de création d\'entreprise', [
            'data' => Arr::except($data, ['admin.password', 'admin.password_confirmation']),
            'timestamp_debut' => $startTime
        ]);

        try {
            // Définir un délai d'attente pour les verrous
            DB::statement('SET innodb_lock_wait_timeout = 30');
            
            // Start transaction with a shorter lock timeout
            DB::beginTransaction();
            
            $entrepriseStartTime = microtime(true);
            // 1. Create enterprise with minimal data first
            $entreprise = $this->createEntrepriseRecord($data);
            Log::channel('queries')->info('Entreprise créée', [
                'temps_execution' => microtime(true) - $entrepriseStartTime,
                'entreprise_id' => $entreprise->id
            ]);
            
            $abonnementStartTime = microtime(true);
            // 2. Create subscription asynchronously
            $abonnement = $this->createAbonnement($entreprise, $data);
            Log::channel('queries')->info('Abonnement créé', [
                'temps_execution' => microtime(true) - $abonnementStartTime,
                'abonnement_id' => $abonnement->id
            ]);
            
            $adminStartTime = microtime(true);
            // 3. Create administrator in parallel
            $this->createAdministrateur($entreprise, $data['admin']);
            Log::channel('queries')->info('Administrateur créé', [
                'temps_execution' => microtime(true) - $adminStartTime
            ]);
            
            // Commit the transaction
            DB::commit();
            
            // Queue non-critical tasks
            dispatch(function () use ($entreprise) {
                // Additional setup tasks
                $this->setupStorageDirectories($entreprise);
                $this->initializeConfigurations($entreprise);
            })->afterCommit();

            Log::channel('queries')->info('Fin de création d\'entreprise', [
                'temps_execution_total' => microtime(true) - $startTime,
                'entreprise_id' => $entreprise->id
            ]);

            return $entreprise;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::channel('queries')->error('Erreur lors de la création de l\'entreprise', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'temps_execution' => microtime(true) - $startTime
            ]);
            throw $e;
        }
    }

    protected function createEntrepriseRecord(array $data): Entreprise
    {
        $entrepriseData = $this->prepareEntrepriseData($data);
        
        // S'assurer que le code est généré si non fourni
        if (empty($entrepriseData['code'])) {
            $nom = preg_replace('/[^A-Za-z0-9]/', '', $entrepriseData['nom']);
            $entrepriseData['code'] = strtoupper(substr($nom, 0, 3));
            Log::info('Code entreprise généré dans le service', [
                'nom' => $entrepriseData['nom'],
                'code' => $entrepriseData['code']
            ]);
        }

        $entreprise = Entreprise::create($entrepriseData);
        
        // Créer le répertoire de stockage
        $storageDir = 'entreprises/' . $entreprise->id;
        if (!Storage::exists($storageDir)) {
            Storage::makeDirectory($storageDir);
            Log::info('Répertoire de stockage créé', ['directory' => $storageDir]);
        }
        
        return $entreprise;
    }

    protected function prepareEntrepriseData(array $data): array
    {
        // Valider l'email
        if (empty($data['admin']['email'])) {
            throw new \InvalidArgumentException('L\'email de l\'administrateur est requis');
        }

        $email = filter_var($data['admin']['email'], FILTER_VALIDATE_EMAIL);
        if (!$email) {
            throw new \InvalidArgumentException('L\'adresse email fournie n\'est pas valide');
        }

        // Vérifier le domaine de l'email
        $domain = substr(strrchr($email, "@"), 1);
        if (!checkdnsrr($domain, 'MX')) {
            throw new \InvalidArgumentException('Le domaine de l\'email n\'est pas valide ou n\'existe pas');
        }

        // Configuration par défaut
        $configuration = [
            'notifications_email' => filter_var($data['notifications_email'] ?? true, FILTER_VALIDATE_BOOLEAN),
            'notifications_sms' => filter_var($data['notifications_sms'] ?? false, FILTER_VALIDATE_BOOLEAN),
            'langue' => in_array($data['langue'] ?? 'fr', ['fr', 'en']) ? $data['langue'] ?? 'fr' : 'fr',
            'devise' => in_array($data['devise'] ?? 'XOF', ['XOF', 'EUR', 'USD']) ? $data['devise'] ?? 'XOF' : 'XOF',
            'format_date' => 'Y-m-d',
            'fuseau_horaire' => $data['fuseau_horaire'] ?? 'UTC',
        ];

        // Traiter le logo
        $logo = null;
        if (isset($data['logo'])) {
            if (is_array($data['logo']) && !empty($data['logo'])) {
                $logo = is_string($data['logo'][0]) ? $data['logo'][0] : null;
            } elseif (is_string($data['logo'])) {
                $logo = $data['logo'];
            }
        }

        // Valider les champs requis
        $requiredFields = ['nom', 'telephone', 'secteur_activite', 'ville', 'pays'];
        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                throw new \InvalidArgumentException("Le champ '{$field}' est requis");
            }
        }

        return [
            'identifiant' => Str::random(10),
            'nom' => trim(strval($data['nom'])),
            'nif' => isset($data['nif']) ? trim(strval($data['nif'])) : null,
            'rccm' => isset($data['rccm']) ? trim(strval($data['rccm'])) : null,
            'raison_sociale' => isset($data['raison_sociale']) ? trim(strval($data['raison_sociale'])) : null,
            'email' => $email,
            'telephone' => trim(strval($data['telephone'])),
            'site_web' => $this->formatWebsiteUrl($data['site_web'] ?? null),
            'secteur_activite' => trim(strval($data['secteur_activite'])),
            'description' => isset($data['description']) ? trim(strval($data['description'])) : null,
            'adresse' => isset($data['adresse']) ? trim(strval($data['adresse'])) : null,
            'complement_adresse' => isset($data['complement_adresse']) ? trim(strval($data['complement_adresse'])) : null,
            'code_postal' => isset($data['code_postal']) ? trim(strval($data['code_postal'])) : null,
            'ville' => trim(strval($data['ville'])),
            'pays' => trim(strval($data['pays'])),
            'fuseau_horaire' => trim(strval($data['fuseau_horaire'] ?? 'UTC')),
            'langue' => trim(strval($data['langue'] ?? 'fr')),
            'logo' => $logo,
            'statut' => 'actif',
            'configuration' => json_encode($configuration, JSON_THROW_ON_ERROR),
        ];
    }

    protected function createAbonnement(Entreprise $entreprise, array $data): Abonnement
    {
        try {
            // Validate required subscription data
            if (!isset($data['date_debut'], $data['periode_facturation'], $data['nombre_personnels'])) {
                throw new \InvalidArgumentException("Données d'abonnement incomplètes");
            }

            // Determine plan and validate personnel count
            $planId = $this->determinerPlanAbonnement($data['nombre_personnels']);
            
            // Get plan directly with where clause instead of findOrFail
            $planAbonnement = PlanAbonnement::where('id', $planId)
                ->where('statut', 'actif')
                ->firstOrFail();

            // Calculate subscription amount based on period
            $montant = $this->calculerMontantAbonnement($planAbonnement, $data['periode_facturation'], $data['nombre_personnels']);

            // Create subscription with minimal data
            $abonnement = Abonnement::create([
                'entreprise_id' => $entreprise->id,
                'plan_abonnement_id' => $planAbonnement->id,
                'date_debut' => $data['date_debut'],
                'date_fin' => $this->calculerDateFin($data['date_debut'], $data['periode_facturation']),
                'statut' => 'actif',
                'montant' => $montant,
                'type_periode' => $data['periode_facturation'],
                'periode_facturation' => $data['periode_facturation'],
                'nombre_personnels' => $data['nombre_personnels'],
                'renouvellement_automatique' => true,
                'facture_automatique' => true
            ]);

            Log::info('Nouvel abonnement créé', [
                'entreprise_id' => $entreprise->id,
                'plan' => $planAbonnement->nom,
                'montant' => $montant
            ]);

            return $abonnement;

        } catch (\Exception $e) {
            Log::error('Erreur abonnement: ' . $e->getMessage());
            throw $e;
        }
    }

    protected function createAdministrateur(Entreprise $entreprise, array $adminData): void
    {
        try {
            // 1. Générer un code employé unique
            Log::info('Début génération code employé', [
                'entreprise_nom' => $entreprise->nom,
                'entreprise_id' => $entreprise->id
            ]);

            // Nettoyer et préparer le préfixe
            $nomNettoye = trim(preg_replace('/[^A-Za-z0-9]/', '', $entreprise->nom));
            if (empty($nomNettoye)) {
                throw new \InvalidArgumentException('Le nom de l\'entreprise ne contient pas de caractères valides pour générer un préfixe');
            }
            
            $entreprisePrefix = strtoupper(substr($nomNettoye, 0, 3));
            if (strlen($entreprisePrefix) < 3) {
                $entreprisePrefix = str_pad($entreprisePrefix, 3, 'X');
            }
            
            Log::info('Préfixe généré', [
                'nom_original' => $entreprise->nom,
                'nom_nettoye' => $nomNettoye,
                'prefixe' => $entreprisePrefix
            ]);

            // Trouver le dernier numéro de séquence utilisé pour cette entreprise
            $lastEmploye = Employeur::where('code_employe', 'LIKE', 'EMP-' . $entreprisePrefix . '-%')
                ->orderByRaw('CAST(SUBSTRING(code_employe, -6) AS UNSIGNED) DESC')
                ->first();

            $sequence = $lastEmploye 
                ? (int)substr($lastEmploye->code_employe, -6) + 1 
                : 250001;

            $codeEmploye = sprintf('EMP-%s-%06d', $entreprisePrefix, $sequence);
            
            Log::info('Code employé final généré', [
                'code_employe' => $codeEmploye,
                'sequence' => $sequence
            ]);

            // Vérification finale d'unicité
            if (Employeur::where('code_employe', $codeEmploye)->exists()) {
                throw new \RuntimeException('Code employé déjà utilisé malgré les vérifications : ' . $codeEmploye);
            }

            // 2. Créer l'employeur
            $employeur = new Employeur();
            $employeur->fill([
                'entreprise_id' => $entreprise->id,
                'nom' => $adminData['nom'],
                'prenom' => $adminData['prenom'],
                'email' => $adminData['email'],
                'telephone' => $adminData['telephone'] ?? null,
                'matricule' => 'ADM-' . Str::random(6),
                'date_embauche' => now(),
                'code_employe' => $codeEmploye,
                'qr_code_secret' => Str::random(32),
                'qr_code_expires_at' => now()->addDays(30),
                'qr_code_active' => true,
                'type_contrat' => 'CDI',
                'statut' => 'actif',
                'poste' => 'Directeur Général',
                'salaire_base' => 1000000,
                'date_naissance' => '1980-01-01',
                'lieu_naissance' => 'Abidjan',
                'genre' => 'M',
                'nationalite' => 'Ivoirienne', // Valeur par défaut
                'type_piece' => 'CNI',
                'numero_piece' => 'C' . Str::random(9),
                'meta_donnees' => json_encode([
                    'situation_matrimoniale' => 'non spécifié',
                    'contact_urgence' => $adminData['telephone'] ?? null,
                    'nom_contact_urgence' => $adminData['nom'] . ' ' . $adminData['prenom']
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
                ])
            ]);

            // Vérification avant sauvegarde
            Log::info('Vérification avant sauvegarde', [
                'code_employe_genere' => $codeEmploye,
                'code_employe_model' => $employeur->code_employe
            ]);

            if ($employeur->code_employe !== $codeEmploye) {
                throw new \RuntimeException('Incohérence détectée : le code employé a été modifié avant la sauvegarde');
            }

            $employeur->save();

            Log::info('Employeur créé avec succès', [
                'id' => $employeur->id,
                'code_employe' => $employeur->code_employe
            ]);

            // 3. Créer l'utilisateur
            $user = User::create([
                'name' => $adminData['prenom'] . ' ' . $adminData['nom'],
                'email' => $adminData['email'],
                'telephone' => $adminData['telephone'] ?? null,
                'password' => Hash::make($adminData['password']),
                'pin' => Hash::make('1234'), // PIN par défaut
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

            // 4. Assigner le rôle administrateur
            $role = Role::where('name', 'admin')->first();
            if ($role) {
                $user->roles()->attach($role->id);
            }

            Log::info('Administrateur créé avec succès', [
                'entreprise_id' => $entreprise->id,
                'employeur_id' => $employeur->id,
                'user_id' => $user->id,
                'code_employe' => $codeEmploye
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la création de l\'administrateur', [
                'error' => $e->getMessage(),
                'entreprise_id' => $entreprise->id
            ]);
            throw $e;
        }
    }

    protected function determinerPlanAbonnement(int $nombrePersonnels): int
    {
        try {
            // Récupérer tous les plans actifs pour le debug
            $plansActifs = PlanAbonnement::where('statut', 'actif')->get();
            Log::info('Plans actifs disponibles', [
                'plans' => $plansActifs->map(function($plan) {
                    return [
                        'id' => $plan->id,
                        'nom' => $plan->nom,
                        'statut' => $plan->statut,
                        'nombre_employes_min' => $plan->nombre_employes_min,
                        'nombre_employes_max' => $plan->nombre_employes_max
                    ];
                })
            ]);

            $query = PlanAbonnement::where('statut', 'actif');

            $planNom = null;
            if ($nombrePersonnels <= 50) {
                $planNom = 'Starter';
            } elseif ($nombrePersonnels <= 100) {
                $planNom = 'Business';
            } else {
                $planNom = 'Entreprise';
            }

            Log::info('Recherche de plan', [
                'nombrePersonnels' => $nombrePersonnels,
                'planNom' => $planNom,
                'sql' => $query->toSql(),
                'bindings' => $query->getBindings()
            ]);

            $plan = $query->where('nom', $planNom)->first();

            if (!$plan) {
                Log::error('Plan non trouvé', [
                    'nombrePersonnels' => $nombrePersonnels,
                    'planNom' => $planNom,
                    'plans_disponibles' => $plansActifs->pluck('nom', 'id')->toArray(),
                    'recherche' => [
                        'sql' => $query->toSql(),
                        'bindings' => $query->getBindings()
                    ]
                ]);
                throw new \RuntimeException("Plan d'abonnement {$planNom} non trouvé pour {$nombrePersonnels} personnels");
            }

            Log::info('Plan trouvé', [
                'plan_id' => $plan->id,
                'plan_nom' => $plan->nom,
                'nombrePersonnels' => $nombrePersonnels,
                'nombre_employes_min' => $plan->nombre_employes_min,
                'nombre_employes_max' => $plan->nombre_employes_max
            ]);

            return (int) $plan->id;

        } catch (\Exception $e) {
            Log::error('Erreur lors de la détermination du plan d\'abonnement', [
                'error' => $e->getMessage(),
                'nombrePersonnels' => $nombrePersonnels,
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    protected function verifierCodePromo(array $data): array
    {
        if (empty($data['code_promo']) && empty($data['code_promo_id'])) {
            return ['id' => null, 'reduction' => null];
        }

        try {
            $codePromo = empty($data['code_promo_id']) 
                ? CodePromo::where('code', $data['code_promo'])->first()
                : CodePromo::find($data['code_promo_id']);

            if (!$codePromo) {
                throw new \RuntimeException('Code promo non trouvé');
            }

            if (!$codePromo->estValide()) {
                throw new \RuntimeException('Code promo expiré ou invalide');
            }

            if ($codePromo->nombre_utilisations_max && $codePromo->nombre_utilisations >= $codePromo->nombre_utilisations_max) {
                throw new \RuntimeException('Code promo a atteint le nombre maximum d\'utilisations');
            }

            $codePromo->incrementerUtilisations();
            
            return [
                'id' => $codePromo->id,
                'reduction' => $codePromo->reduction
            ];

        } catch (\Exception $e) {
            Log::warning('Erreur lors de la vérification du code promo', [
                'error' => $e->getMessage(),
                'code' => $data['code_promo'] ?? null,
                'id' => $data['code_promo_id'] ?? null
            ]);
            return ['id' => null, 'reduction' => null];
        }
    }

    protected function calculerMontantAbonnement(PlanAbonnement $plan, string $periode, int $nombrePersonnels): float
    {
        $prixBase = match($periode) {
            'mensuel' => $plan->prix_mensuel,
            'trimestriel' => $plan->prix_trimestriel,
            'semestriel' => $plan->prix_semestriel,
            'annuel' => $plan->prix_annuel,
            default => throw new \InvalidArgumentException("Période de facturation invalide: {$periode}")
        };

        // Add per-user cost if applicable
        if ($plan->cout_par_employe > 0) {
            $prixBase += ($plan->cout_par_employe * $nombrePersonnels);
        }

        return round($prixBase, 2);
    }

    protected function calculerDateFin(string $dateDebut, string $periode): string
    {
        $periodes = [
            'mensuel' => '+1 month',
            'trimestriel' => '+3 months',
            'semestriel' => '+6 months',
            'annuel' => '+1 year'
        ];
        
        return date('Y-m-d', strtotime($dateDebut . ' ' . ($periodes[$periode] ?? '+1 month')));
    }

    protected function formatWebsiteUrl(?string $url): ?string
    {
        if (empty($url)) {
            return null;
        }

        // Nettoyer l'URL
        $url = preg_replace('#^https?://#', '', $url);
        $url = 'https://' . $url;
        
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            throw new \InvalidArgumentException('L\'URL du site web n\'est pas valide. Format attendu: example.com');
        }

        return $url;
    }

    protected function setupStorageDirectories(Entreprise $entreprise): void
    {
        // Créer le répertoire de stockage
        $storageDir = 'entreprises/' . $entreprise->id;
        if (!Storage::exists($storageDir)) {
            Storage::makeDirectory($storageDir);
            Log::info('Répertoire de stockage créé', ['directory' => $storageDir]);
        }
    }

    protected function initializeConfigurations(Entreprise $entreprise): void
    {
        // Initialize configurations
    }

    public function renouvelerAbonnement(Abonnement $abonnement): Abonnement
    {
        try {
            DB::beginTransaction();

            // Verify current subscription status
            if (!$abonnement->isExpire() && $abonnement->statut === 'actif') {
                throw new \RuntimeException('L\'abonnement est encore actif');
            }

            // Get current plan and validate
            $plan = $abonnement->planAbonnement;
            if (!$plan || $plan->statut !== 'actif') {
                throw new \RuntimeException('Plan d\'abonnement invalide ou inactif');
            }

            // Calculate new subscription dates
            $dateDebut = $abonnement->isExpire() ? now() : $abonnement->date_fin;
            $dateFin = $this->calculerDateFin(
                $dateDebut->format('Y-m-d'),
                $abonnement->periode_facturation
            );

            // Calculate new amount
            $montant = $this->calculerMontantAbonnement(
                $plan,
                $abonnement->periode_facturation,
                $abonnement->nombre_personnels
            );

            // Create new subscription
            $nouvelAbonnement = Abonnement::create([
                'entreprise_id' => $abonnement->entreprise_id,
                'plan_abonnement_id' => $plan->id,
                'date_debut' => $dateDebut,
                'date_fin' => $dateFin,
                'statut' => 'actif',
                'montant' => $montant,
                'type_periode' => $abonnement->periode_facturation,
                'renouvellement_automatique' => $abonnement->renouvellement_automatique,
                'periode_facturation' => $abonnement->periode_facturation,
                'facture_automatique' => $abonnement->facture_automatique,
                'nombre_personnels' => $abonnement->nombre_personnels,
                'notes' => 'Renouvellement automatique de l\'abonnement #' . $abonnement->id,
            ]);

            // Update old subscription
            $abonnement->update(['statut' => 'termine']);

            DB::commit();

            // Log renewal
            Log::info('Abonnement renouvelé', [
                'ancien_abonnement_id' => $abonnement->id,
                'nouvel_abonnement_id' => $nouvelAbonnement->id,
                'entreprise_id' => $abonnement->entreprise_id,
                'montant' => $montant
            ]);

            return $nouvelAbonnement;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors du renouvellement de l\'abonnement', [
                'error' => $e->getMessage(),
                'abonnement_id' => $abonnement->id
            ]);
            throw new \RuntimeException('Erreur lors du renouvellement : ' . $e->getMessage());
        }
    }
}
