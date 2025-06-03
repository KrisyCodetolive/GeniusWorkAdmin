<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Employeur;
use App\Models\Entreprise;
use App\Models\Filiale;
use App\Models\Departement;
use App\Models\Site;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class EmployeurExempleSeeder extends Seeder
{
    /**
     * Liste des employés basiques à créer comme exemples
     * @param Entreprise $entreprise L'entreprise pour laquelle générer les employés
     * @return array Liste des employés avec des données uniques pour cette entreprise
     */
    public static function getEmployeursExemples($entreprise)
    {
        // Générer un identifiant unique pour cette entreprise (pour les emails)
        $entrepriseCode = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $entreprise->nom));
        if (strlen($entrepriseCode) < 3) {
            $entrepriseCode .= substr(md5($entreprise->id), 0, 3);
        }
        
        // Générer un préfixe de téléphone unique pour cette entreprise
        $phonePrefix = '+225 0' . rand(1, 9);
        
        return [
            [
                'nom' => 'Kouassi',
                'prenom' => 'Aya',
                'poste' => 'Directrice des Ressources Humaines',
                'email' => 'aya.kouassi@' . $entrepriseCode . '.com',
                'telephone' => $phonePrefix . ' ' . rand(10, 99) . ' ' . rand(10, 99) . ' ' . rand(10, 99) . ' ' . rand(10, 99),
                'date_naissance' => '1985-05-15',
                'lieu_naissance' => 'Abidjan',
                'genre' => 'F',
                'type_contrat' => 'cdi',
                'date_embauche' => '2018-03-01',
                'salaire_base' => 1500000,
                'statut' => 'actif',
                'departement' => 'Ressources Humaines',
            ],
            [
                'nom' => 'Koné',
                'prenom' => 'Ibrahim',
                'poste' => 'Directeur Financier',
                'email' => 'ibrahim.kone@' . $entrepriseCode . '.com',
                'telephone' => $phonePrefix . ' ' . rand(10, 99) . ' ' . rand(10, 99) . ' ' . rand(10, 99) . ' ' . rand(10, 99),
                'date_naissance' => '1980-08-22',
                'lieu_naissance' => 'Bouaké',
                'genre' => 'M',
                'type_contrat' => 'cdi',
                'date_embauche' => '2017-06-15',
                'salaire_base' => 1800000,
                'statut' => 'actif',
                'departement' => 'Finance',
            ],
            [
                'nom' => 'Bamba',
                'prenom' => 'Mariam',
                'poste' => 'Responsable Marketing',
                'email' => 'mariam.bamba@' . $entrepriseCode . '.com',
                'telephone' => $phonePrefix . ' ' . rand(10, 99) . ' ' . rand(10, 99) . ' ' . rand(10, 99) . ' ' . rand(10, 99),
                'date_naissance' => '1988-11-30',
                'lieu_naissance' => 'Abidjan',
                'genre' => 'F',
                'type_contrat' => 'cdi',
                'date_embauche' => '2019-02-10',
                'salaire_base' => 1200000,
                'statut' => 'actif',
                'departement' => 'Marketing',
            ],
            [
                'nom' => 'Touré',
                'prenom' => 'Amadou',
                'poste' => 'Ingénieur Informatique',
                'email' => 'amadou.toure@' . $entrepriseCode . '.com',
                'telephone' => $phonePrefix . ' ' . rand(10, 99) . ' ' . rand(10, 99) . ' ' . rand(10, 99) . ' ' . rand(10, 99),
                'date_naissance' => '1990-04-18',
                'lieu_naissance' => 'Yamoussoukro',
                'genre' => 'M',
                'type_contrat' => 'cdi',
                'date_embauche' => '2020-01-15',
                'salaire_base' => 1100000,
                'statut' => 'actif',
                'departement' => 'Informatique',
            ],
            [
                'nom' => 'Diallo',
                'prenom' => 'Fatou',
                'poste' => 'Comptable',
                'email' => 'fatou.diallo@' . $entrepriseCode . '.com',
                'telephone' => $phonePrefix . ' ' . rand(10, 99) . ' ' . rand(10, 99) . ' ' . rand(10, 99) . ' ' . rand(10, 99),
                'date_naissance' => '1992-09-05',
                'lieu_naissance' => 'Abidjan',
                'genre' => 'F',
                'type_contrat' => 'cdi',
                'date_embauche' => '2021-05-01',
                'salaire_base' => 900000,
                'statut' => 'actif',
                'departement' => 'Finance',
            ],
            [
                'nom' => 'Ouattara',
                'prenom' => 'Seydou',
                'poste' => 'Responsable Commercial',
                'email' => 'seydou.ouattara@' . $entrepriseCode . '.com',
                'telephone' => $phonePrefix . ' ' . rand(10, 99) . ' ' . rand(10, 99) . ' ' . rand(10, 99) . ' ' . rand(10, 99),
                'date_naissance' => '1987-07-12',
                'lieu_naissance' => 'Korhogo',
                'genre' => 'M',
                'type_contrat' => 'cdi',
                'date_embauche' => '2018-09-15',
                'salaire_base' => 1300000,
                'statut' => 'actif',
                'departement' => 'Commercial',
            ],
            [
                'nom' => 'Yao',
                'prenom' => 'Kouadio',
                'poste' => 'Technicien Maintenance',
                'email' => 'kouadio.yao@' . $entrepriseCode . '.com',
                'telephone' => $phonePrefix . ' ' . rand(10, 99) . ' ' . rand(10, 99) . ' ' . rand(10, 99) . ' ' . rand(10, 99),
                'date_naissance' => '1991-12-03',
                'lieu_naissance' => 'Daloa',
                'genre' => 'M',
                'type_contrat' => 'cdd',
                'date_embauche' => '2022-03-01',
                'salaire_base' => 700000,
                'statut' => 'actif',
                'departement' => 'Technique',
            ],
            [
                'nom' => 'Konaté',
                'prenom' => 'Aminata',
                'poste' => 'Assistante de Direction',
                'email' => 'aminata.konate@' . $entrepriseCode . '.com',
                'telephone' => $phonePrefix . ' ' . rand(10, 99) . ' ' . rand(10, 99) . ' ' . rand(10, 99) . ' ' . rand(10, 99),
                'date_naissance' => '1993-02-25',
                'lieu_naissance' => 'Abidjan',
                'genre' => 'F',
                'type_contrat' => 'cdi',
                'date_embauche' => '2020-11-01',
                'salaire_base' => 800000,
                'statut' => 'actif',
                'departement' => 'Direction',
            ],
            [
                'nom' => 'Traoré',
                'prenom' => 'Moussa',
                'poste' => 'Chef de Projet',
                'email' => 'moussa.traore@' . $entrepriseCode . '.com',
                'telephone' => $phonePrefix . ' ' . rand(10, 99) . ' ' . rand(10, 99) . ' ' . rand(10, 99) . ' ' . rand(10, 99),
                'date_naissance' => '1986-06-17',
                'lieu_naissance' => 'Abidjan',
                'genre' => 'M',
                'type_contrat' => 'cdi',
                'date_embauche' => '2019-07-01',
                'salaire_base' => 1400000,
                'statut' => 'actif',
                'departement' => 'Informatique',
            ],
            [
                'nom' => 'Coulibaly',
                'prenom' => 'Rokia',
                'poste' => 'Responsable Logistique',
                'email' => 'rokia.coulibaly@' . $entrepriseCode . '.com',
                'telephone' => $phonePrefix . ' ' . rand(10, 99) . ' ' . rand(10, 99) . ' ' . rand(10, 99) . ' ' . rand(10, 99),
                'date_naissance' => '1989-10-08',
                'lieu_naissance' => 'Abidjan',
                'genre' => 'F',
                'type_contrat' => 'cdi',
                'date_embauche' => '2018-12-01',
                'salaire_base' => 1100000,
                'statut' => 'actif',
                'departement' => 'Logistique',
            ],
        ];
    }

    /**
     * Seed the application's database with example employeurs.
     */
    public function run()
    {
        // Cette méthode n'est pas utilisée directement
        // Nous utilisons plutôt la méthode createForEntreprise
    }

    /**
     * Crée des employés exemples pour une entreprise spécifique
     */
    public static function createForEntreprise(Entreprise $entreprise)
    {
        $result = [
            'created' => [],
            'existants' => 0,
            'departements' => 0,
            'filiales' => 0,
            'sites' => 0,
            'message' => ''
        ];
        
        // Vérifier d'abord si des employés existent déjà pour cette entreprise
        $existingEmployeursCount = Employeur::where('entreprise_id', $entreprise->id)->count();
        
        if ($existingEmployeursCount > 0) {
            $result['message'] = "Des employés exemples existent déjà pour cette entreprise ({$existingEmployeursCount} employés trouvés)";
            $result['existants'] = $existingEmployeursCount;
            return $result;
        }
        
        // Vérifier si l'entreprise a un abonnement actif et récupérer la limite d'employés
        $abonnementActif = $entreprise->abonnementActif;
        if (!$abonnementActif) {
            $result['message'] = "Votre entreprise n'a pas d'abonnement actif. Veuillez souscrire à un abonnement avant de générer des employés exemples.";
            return $result;
        }
        
        // Récupérer la limite d'employés depuis l'abonnement
        $limit = $entreprise->nombre_employes ?? $abonnementActif->nombre_personnels ?? 
                $abonnementActif->planAbonnement->nombre_employes_max ?? 0;
        
        if ($limit <= 0) {
            $result['message'] = "Votre abonnement ne permet pas de créer des employés. Veuillez contacter le support.";
            return $result;
        }
        
        // Vérifier si des filiales existent, sinon les créer
        $filiales = Filiale::where('entreprise_id', $entreprise->id)->get();
        if ($filiales->isEmpty()) {
            $filialesResult = FilialeExempleSeeder::createForEntreprise($entreprise);
            $filiales = Filiale::where('entreprise_id', $entreprise->id)->get();
            $result['filiales'] = count($filialesResult['created']);
        }
        
        // Vérifier si des sites existent, sinon les créer
        $sites = Site::where('entreprise_id', $entreprise->id)->get();
        if ($sites->isEmpty()) {
            $sitesResult = SiteExempleSeeder::createForEntreprise($entreprise);
            $result['sites'] = count($sitesResult['created']);
        }
        
        // Récupérer la filiale siège
        $filialesSiege = $filiales->where('code', 'like', '%SIEGE%')->first();
        if (!$filialesSiege && $filiales->count() > 0) {
            $filialesSiege = $filiales->first();
        }
        
        // Vérifier si des départements existent pour cette filiale
        $existingDepartements = [];
        $departements = [];
        
        if ($filialesSiege) {
            $existingDepartements = Departement::where('entreprise_id', $entreprise->id)
                ->pluck('id', 'nom')
                ->toArray();
            
            // Si aucun département n'existe, créer les départements exemples
            if (empty($existingDepartements)) {
                $departementsResult = DepartementExempleSeeder::createForFiliale($filialesSiege);
                $existingDepartements = Departement::where('entreprise_id', $entreprise->id)
                    ->pluck('id', 'nom')
                    ->toArray();
                $result['departements'] = count($departementsResult['created']);
            }
        }

        $employeurs = self::getEmployeursExemples($entreprise);
        $created = [];
        
        // Limiter le nombre d'employés à créer en fonction de la limite d'abonnement
        $nombreEmployesACreer = min(count($employeurs), $limit);
        $employeurs = array_slice($employeurs, 0, $nombreEmployesACreer);
        
        foreach ($employeurs as $index => $employeur) {
            // Vérifier si le nombre d'employés créés atteint déjà la limite
            if (count($created) >= $limit) {
                break; // Arrêter la création si la limite est atteinte
            }
            
            // Vérifier si un employé similaire existe déjà pour cette entreprise
            $existant = Employeur::where('entreprise_id', $entreprise->id)
                ->where('nom', $employeur['nom'])
                ->where('prenom', $employeur['prenom'])
                ->first();
            
            if (!$existant) {
                // Trouver le département correspondant ou un département similaire
                $departementNom = $employeur['departement'];
                $departementId = null;
                
                // Recherche exacte
                if (isset($existingDepartements[$departementNom])) {
                    $departementId = $existingDepartements[$departementNom];
                } 
                // Recherche par mot-clé
                else {
                    foreach ($existingDepartements as $nom => $id) {
                        if (stripos($nom, $departementNom) !== false || stripos($departementNom, $nom) !== false) {
                            $departementId = $id;
                            break;
                        }
                    }
                }
                
                // Si aucun département correspondant n'est trouvé, utiliser le premier département
                if (!$departementId && !empty($existingDepartements)) {
                    $departementId = reset($existingDepartements);
                }
                
                // Générer un code employé unique
                $codeEmploye = 'EMP-' . strtoupper(substr($employeur['nom'], 0, 3) . substr($employeur['prenom'], 0, 2)) . 
                               str_pad($index + 1, 3, '0', STR_PAD_LEFT);
                
                // Vérifier si ce code existe déjà dans la base de données
                $codeExists = Employeur::where('code_employe', $codeEmploye)->exists();
                
                // Si le code existe déjà, ajouter un timestamp pour le rendre unique
                if ($codeExists) {
                    $codeEmploye = 'EMP-' . strtoupper(substr($employeur['nom'], 0, 3) . substr($employeur['prenom'], 0, 2)) . 
                                  str_pad($index + 1, 3, '0', STR_PAD_LEFT) . '-' . time();
                }
                
                // Générer un QR code secret
                $qrCodeSecret = Str::uuid()->toString();
                
                $newEmployeur = [
                    'nom' => $employeur['nom'],
                    'prenom' => $employeur['prenom'],
                    'email' => $employeur['email'],
                    'telephone' => $employeur['telephone'],
                    'date_naissance' => $employeur['date_naissance'],
                    'lieu_naissance' => $employeur['lieu_naissance'],
                    'genre' => $employeur['genre'],
                    'poste' => $employeur['poste'],
                    'code_employe' => $codeEmploye,
                    'type_contrat' => $employeur['type_contrat'],
                    'date_embauche' => $employeur['date_embauche'],
                    'salaire_base' => $employeur['salaire_base'],
                    'statut' => $employeur['statut'],
                    'entreprise_id' => $entreprise->id,
                    'filiale_id' => $filialesSiege ? $filialesSiege->id : null,
                    'departement_id' => $departementId,
                    'qr_code_secret' => $qrCodeSecret,
                    'qr_code_expires_at' => Carbon::now()->addYear(),
                    'qr_code_active' => true,
                ];
                
                $createdEmployeur = Employeur::create($newEmployeur);
                $created[] = $createdEmployeur;
            } else {
                $existants++;
            }
        }

        $result['created'] = $created;
        if (empty($result['message'])) {
            if (count($created) > 0) {
                $result['message'] = count($created) . ' employés exemples ont été créés avec succès.';
                
                // Ajouter des informations sur les autres éléments créés
                if ($result['departements'] > 0) {
                    $result['message'] .= ' ' . $result['departements'] . ' départements ont été créés.';
                }
                if ($result['filiales'] > 0) {
                    $result['message'] .= ' ' . $result['filiales'] . ' filiales ont été créées.';
                }
                if ($result['sites'] > 0) {
                    $result['message'] .= ' ' . $result['sites'] . ' sites ont été créés.';
                }
            } else {
                $result['message'] = 'Aucun nouvel employé n\'a été créé. Tous les employés existent déjà.';
            }
        }
        
        return $result;
    }
}
