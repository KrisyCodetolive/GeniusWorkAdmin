<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Filiale;
use App\Models\Entreprise;

class FilialeExempleSeeder extends Seeder
{
    /**
     * Liste des filiales basiques à créer comme exemples
     */
    public static function getFilialesExemples()
    {
        return [
            [
                'nom' => 'Siège Social',
                'description' => 'Siège principal de l\'entreprise',
                'code' => 'SIEGE',
                'ville' => 'Abidjan',
                'pays' => 'Côte d\'Ivoire',
                'adresse' => 'Boulevard de la République, Plateau',
                'telephone' => '+225 27 20 30 40 50',
                'email' => 'siege@entreprise.com',
                'statut' => 'actif',
                'configuration' => [
                    'limite_employes' => '100',
                    'budget' => '0',
                    'type' => 'siege_social',
                ],
            ],
            [
                'nom' => 'Filiale Régionale Nord',
                'description' => 'Filiale couvrant la région Nord',
                'code' => 'NORD',
                'ville' => 'Korhogo',
                'pays' => 'Côte d\'Ivoire',
                'adresse' => 'Avenue des Manguiers',
                'telephone' => '+225 27 36 86 20 30',
                'email' => 'nord@entreprise.com',
                'statut' => 'actif',
                'configuration' => [
                    'limite_employes' => '50',
                    'budget' => '0',
                ],
            ],
            [
                'nom' => 'Filiale Régionale Sud',
                'description' => 'Filiale couvrant la région Sud',
                'code' => 'SUD',
                'ville' => 'San-Pédro',
                'pays' => 'Côte d\'Ivoire',
                'adresse' => 'Rue du Port',
                'telephone' => '+225 27 34 72 15 40',
                'email' => 'sud@entreprise.com',
                'statut' => 'actif',
                'configuration' => [
                    'limite_employes' => '50',
                    'budget' => '0',
                ],
            ],
            [
                'nom' => 'Filiale Régionale Est',
                'description' => 'Filiale couvrant la région Est',
                'code' => 'EST',
                'ville' => 'Abengourou',
                'pays' => 'Côte d\'Ivoire',
                'adresse' => 'Boulevard des Planteurs',
                'telephone' => '+225 27 35 91 45 60',
                'email' => 'est@entreprise.com',
                'statut' => 'actif',
                'configuration' => [
                    'limite_employes' => '40',
                    'budget' => '0',
                ],
            ],
            [
                'nom' => 'Filiale Régionale Ouest',
                'description' => 'Filiale couvrant la région Ouest',
                'code' => 'OUEST',
                'ville' => 'Man',
                'pays' => 'Côte d\'Ivoire',
                'adresse' => 'Avenue des Montagnes',
                'telephone' => '+225 27 33 79 25 50',
                'email' => 'ouest@entreprise.com',
                'statut' => 'actif',
                'configuration' => [
                    'limite_employes' => '40',
                    'budget' => '0',
                ],
            ],
            [
                'nom' => 'Filiale Internationale',
                'description' => 'Filiale pour les opérations internationales',
                'code' => 'INTL',
                'ville' => 'Abidjan',
                'pays' => 'Côte d\'Ivoire',
                'adresse' => 'Zone Franche de Grand-Bassam',
                'telephone' => '+225 27 21 30 40 50',
                'email' => 'international@entreprise.com',
                'statut' => 'actif',
                'configuration' => [
                    'limite_employes' => '75',
                    'budget' => '0',
                ],
            ],
        ];
    }

    /**
     * Seed the application's database with example filiales.
     */
    public function run()
    {
        // Cette méthode n'est pas utilisée directement
        // Nous utilisons plutôt la méthode createForEntreprise
    }

    /**
     * Crée des filiales exemples pour une entreprise spécifique
     */
    public static function createForEntreprise(Entreprise $entreprise)
    {
        $filiales = self::getFilialesExemples();
        $created = [];
        $existants = 0;

        foreach ($filiales as $filiale) {
            // Vérifier si une filiale similaire existe déjà pour cette entreprise
            $existant = Filiale::where('entreprise_id', $entreprise->id)
                ->where('nom', $filiale['nom'])
                ->first();
            
            if (!$existant) {
                // Générer un code unique en combinant le code de base avec un identifiant unique
                // Utiliser les 8 premiers caractères de l'ID de l'entreprise pour créer un code unique
                $entrepriseShortId = substr($entreprise->id, 0, 8);
                $uniqueCode = $filiale['code'] . '_' . $entrepriseShortId;
                
                // Vérifier si ce code existe déjà dans la base de données
                $codeExists = Filiale::where('code', $uniqueCode)->exists();
                
                // Si le code existe déjà, ajouter un timestamp pour le rendre unique
                if ($codeExists) {
                    $uniqueCode = $filiale['code'] . '_' . $entrepriseShortId . '_' . time();
                }
                
                $newFiliale = [
                    'nom' => $filiale['nom'],
                    'description' => $filiale['description'],
                    'code' => $uniqueCode,
                    'ville' => $filiale['ville'],
                    'pays' => $filiale['pays'],
                    'adresse' => $filiale['adresse'],
                    'telephone' => $filiale['telephone'],
                    'email' => $filiale['email'],
                    'entreprise_id' => $entreprise->id,
                    'statut' => $filiale['statut'],
                    'configuration' => $filiale['configuration'],
                ];
                
                $createdFiliale = Filiale::create($newFiliale);
                $created[] = $createdFiliale;
            } else {
                $existants++;
            }
        }

        return [
            'created' => $created,
            'existants' => $existants,
            'total' => count($filiales)
        ];
    }
}
