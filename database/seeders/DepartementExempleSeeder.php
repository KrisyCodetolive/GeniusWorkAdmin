<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Departement;
use App\Models\Entreprise;
use App\Models\Filiale;

class DepartementExempleSeeder extends Seeder
{
    /**
     * Liste des départements basiques à créer comme exemples
     */
    public static function getDepartementsExemples()
    {
        return [
            [
                'nom' => 'Direction Générale',
                'description' => 'Direction et gestion stratégique de l\'entreprise',
                'code' => 'DIR-GEN',
                'niveau' => 1,
                'statut' => 'actif',
                'configuration' => [
                    'limite_employes' => '10',
                    'budget' => '0',
                ],
                'parent_id' => null,
            ],
            [
                'nom' => 'Ressources Humaines',
                'description' => 'Gestion du personnel, recrutement et formation',
                'code' => 'RH',
                'niveau' => 2,
                'statut' => 'actif',
                'configuration' => [
                    'limite_employes' => '15',
                    'budget' => '0',
                ],
                'parent_id' => 'Direction Générale',
            ],
            [
                'nom' => 'Finance et Comptabilité',
                'description' => 'Gestion financière, comptabilité et contrôle de gestion',
                'code' => 'FIN',
                'niveau' => 2,
                'statut' => 'actif',
                'configuration' => [
                    'limite_employes' => '20',
                    'budget' => '0',
                ],
                'parent_id' => 'Direction Générale',
            ],
            [
                'nom' => 'Marketing et Communication',
                'description' => 'Stratégie marketing, communication et relations publiques',
                'code' => 'MKT',
                'niveau' => 2,
                'statut' => 'actif',
                'configuration' => [
                    'limite_employes' => '25',
                    'budget' => '0',
                ],
                'parent_id' => 'Direction Générale',
            ],
            [
                'nom' => 'Informatique et Systèmes d\'Information',
                'description' => 'Gestion des infrastructures IT et développement logiciel',
                'code' => 'IT',
                'niveau' => 2,
                'statut' => 'actif',
                'configuration' => [
                    'limite_employes' => '30',
                    'budget' => '0',
                ],
                'parent_id' => 'Direction Générale',
            ],
            [
                'nom' => 'Production',
                'description' => 'Gestion de la production et des opérations',
                'code' => 'PROD',
                'niveau' => 2,
                'statut' => 'actif',
                'configuration' => [
                    'limite_employes' => '50',
                    'budget' => '0',
                ],
                'parent_id' => 'Direction Générale',
            ],
            [
                'nom' => 'Recherche et Développement',
                'description' => 'Innovation et développement de nouveaux produits',
                'code' => 'R&D',
                'niveau' => 2,
                'statut' => 'actif',
                'configuration' => [
                    'limite_employes' => '20',
                    'budget' => '0',
                ],
                'parent_id' => 'Direction Générale',
            ],
            [
                'nom' => 'Commercial et Ventes',
                'description' => 'Gestion des ventes et relations clients',
                'code' => 'COM',
                'niveau' => 2,
                'statut' => 'actif',
                'configuration' => [
                    'limite_employes' => '40',
                    'budget' => '0',
                ],
                'parent_id' => 'Direction Générale',
            ],
            [
                'nom' => 'Logistique et Achats',
                'description' => 'Gestion de la chaîne d\'approvisionnement et des achats',
                'code' => 'LOG',
                'niveau' => 2,
                'statut' => 'actif',
                'configuration' => [
                    'limite_employes' => '25',
                    'budget' => '0',
                ],
                'parent_id' => 'Direction Générale',
            ],
            [
                'nom' => 'Qualité',
                'description' => 'Contrôle qualité et amélioration continue',
                'code' => 'QUA',
                'niveau' => 2,
                'statut' => 'actif',
                'configuration' => [
                    'limite_employes' => '15',
                    'budget' => '0',
                ],
                'parent_id' => 'Direction Générale',
            ],
            [
                'nom' => 'Juridique',
                'description' => 'Affaires juridiques et conformité',
                'code' => 'JUR',
                'niveau' => 2,
                'statut' => 'actif',
                'configuration' => [
                    'limite_employes' => '10',
                    'budget' => '0',
                ],
                'parent_id' => 'Direction Générale',
            ],
            [
                'nom' => 'Recrutement',
                'description' => 'Processus de recrutement et sélection des candidats',
                'code' => 'RH-REC',
                'niveau' => 3,
                'statut' => 'actif',
                'configuration' => [
                    'limite_employes' => '5',
                    'budget' => '0',
                ],
                'parent_id' => 'Ressources Humaines',
            ],
            [
                'nom' => 'Formation',
                'description' => 'Gestion des formations et développement des compétences',
                'code' => 'RH-FOR',
                'niveau' => 3,
                'statut' => 'actif',
                'configuration' => [
                    'limite_employes' => '5',
                    'budget' => '0',
                ],
                'parent_id' => 'Ressources Humaines',
            ],
            [
                'nom' => 'Administration du Personnel',
                'description' => 'Gestion administrative des employés',
                'code' => 'RH-ADM',
                'niveau' => 3,
                'statut' => 'actif',
                'configuration' => [
                    'limite_employes' => '5',
                    'budget' => '0',
                ],
                'parent_id' => 'Ressources Humaines',
            ],
            [
                'nom' => 'Comptabilité Générale',
                'description' => 'Tenue des comptes et reporting financier',
                'code' => 'FIN-CG',
                'niveau' => 3,
                'statut' => 'actif',
                'configuration' => [
                    'limite_employes' => '8',
                    'budget' => '0',
                ],
                'parent_id' => 'Finance et Comptabilité',
            ],
            [
                'nom' => 'Contrôle de Gestion',
                'description' => 'Suivi budgétaire et analyse de la performance',
                'code' => 'FIN-CDG',
                'niveau' => 3,
                'statut' => 'actif',
                'configuration' => [
                    'limite_employes' => '6',
                    'budget' => '0',
                ],
                'parent_id' => 'Finance et Comptabilité',
            ],
            [
                'nom' => 'Trésorerie',
                'description' => 'Gestion des flux financiers et relations bancaires',
                'code' => 'FIN-TRE',
                'niveau' => 3,
                'statut' => 'actif',
                'configuration' => [
                    'limite_employes' => '4',
                    'budget' => '0',
                ],
                'parent_id' => 'Finance et Comptabilité',
            ],
        ];
    }

    /**
     * Seed the application's database with example departments.
     */
    public function run()
    {
        // Cette méthode n'est pas utilisée directement
        // Nous utilisons plutôt la méthode createForFiliale
    }

    /**
     * Crée des départements exemples pour une filiale spécifique
     */
    public static function createForFiliale(Filiale $filiale)
    {
        $departements = self::getDepartementsExemples();
        $created = [];
        $existants = 0;
        $departementMap = []; // Pour stocker les correspondances entre noms et IDs

        // Première passe : créer les départements sans parent
        foreach ($departements as $departement) {
            // Vérifier si un département similaire existe déjà pour cette filiale
            $existant = Departement::where('filiale_id', $filiale->id)
                ->where('nom', $departement['nom'])
                ->first();
            
            if (!$existant) {
                // Générer un code unique en combinant le code de base avec un identifiant unique
                // Utiliser les 8 premiers caractères de l'ID de l'entreprise pour créer un code unique
                $entrepriseShortId = substr($filiale->entreprise_id, 0, 8);
                $uniqueCode = $departement['code'] . '_' . $entrepriseShortId;
                
                // Vérifier si ce code existe déjà dans la base de données
                $codeExists = Departement::where('code', $uniqueCode)->exists();
                
                // Si le code existe déjà, ajouter un timestamp pour le rendre unique
                if ($codeExists) {
                    $uniqueCode = $departement['code'] . '_' . $entrepriseShortId . '_' . time();
                }
                
                $newDepartement = [
                    'nom' => $departement['nom'],
                    'description' => $departement['description'],
                    'code' => $uniqueCode,
                    'filiale_id' => $filiale->id,
                    'entreprise_id' => $filiale->entreprise_id,
                    'statut' => $departement['statut'],
                    'configuration' => $departement['configuration'],
                    'parent_id' => null, // On définira les relations parent-enfant dans une seconde passe
                    'niveau' => $departement['niveau'] ?? 1, // Assurer que le niveau n'est jamais null
                ];
                
                $createdDepartement = Departement::create($newDepartement);
                $created[] = $createdDepartement;
                $departementMap[$departement['nom']] = $createdDepartement->id;
            } else {
                $existants++;
                $departementMap[$departement['nom']] = $existant->id;
            }
        }

        // Deuxième passe : mettre à jour les relations parent-enfant
        foreach ($departements as $departement) {
            if ($departement['parent_id'] !== null && isset($departementMap[$departement['nom']])) {
                $departementId = $departementMap[$departement['nom']];
                $parentId = $departementMap[$departement['parent_id']] ?? null;
                
                if ($parentId) {
                    $departementObj = Departement::find($departementId);
                    if ($departementObj) {
                        $departementObj->update([
                            'parent_id' => $parentId,
                            'niveau' => $departement['niveau'],
                        ]);
                    }
                }
            }
        }

        $result = [
            'created' => $created,
            'existants' => $existants,
            'total' => count($departements),
            'message' => ''
        ];
        
        if (count($created) > 0) {
            $result['message'] = count($created) . ' départements ont été créés avec succès.';
            if ($existants > 0) {
                $result['message'] .= ' ' . $existants . ' départements existaient déjà.';
            }
        } else {
            $result['message'] = 'Aucun nouveau département n\'a été créé. Tous les départements existent déjà.';
        }
        
        return $result;
    }
}
