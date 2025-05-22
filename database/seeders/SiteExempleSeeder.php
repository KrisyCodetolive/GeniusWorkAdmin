<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Site;
use App\Models\Entreprise;

class SiteExempleSeeder extends Seeder
{
    /**
     * Liste des sites basiques à créer comme exemples
     */
    public static function getSitesExemples()
    {
        return [
            [
                'nom' => 'Siège Principal',
                'description' => 'Siège social principal de l\'entreprise',
                'adresse' => '123 Boulevard de la République',
                'code_postal' => '01 BP 1234',
                'ville' => 'Abidjan',
                'pays' => 'Côte d\'Ivoire',
                'telephone' => '+225 27 20 30 40 50',
                'email' => 'contact@entreprise.com',
                'has_geofencing' => true,
                'geofencing_radius' => 100,
                'latitude' => 5.3364,
                'longitude' => -4.0267,
                'statut' => 'actif',
                'configuration' => [
                    'capacite_max' => '200',
                    'horaires_ouverture' => '08:00-17:00',
                ],
            ],
            [
                'nom' => 'Centre Technique',
                'description' => 'Centre de recherche et développement',
                'adresse' => '45 Rue des Jardins, Zone 4',
                'code_postal' => '04 BP 2345',
                'ville' => 'Abidjan',
                'pays' => 'Côte d\'Ivoire',
                'telephone' => '+225 27 20 25 30 35',
                'email' => 'technique@entreprise.com',
                'has_geofencing' => true,
                'geofencing_radius' => 75,
                'latitude' => 5.3016,
                'longitude' => -3.9986,
                'statut' => 'actif',
                'configuration' => [
                    'capacite_max' => '150',
                    'horaires_ouverture' => '08:00-18:00',
                ],
            ],
            [
                'nom' => 'Entrepôt Logistique',
                'description' => 'Centre de stockage et distribution',
                'adresse' => '789 Zone Industrielle de Yopougon',
                'code_postal' => '21 BP 3456',
                'ville' => 'Abidjan',
                'pays' => 'Côte d\'Ivoire',
                'telephone' => '+225 27 20 45 50 55',
                'email' => 'logistique@entreprise.com',
                'has_geofencing' => true,
                'geofencing_radius' => 150,
                'latitude' => 5.3372,
                'longitude' => -4.0865,
                'statut' => 'actif',
                'configuration' => [
                    'capacite_max' => '300',
                    'horaires_ouverture' => '06:00-20:00',
                ],
            ],
            [
                'nom' => 'Bureau Commercial',
                'description' => 'Bureau des ventes et relations clients',
                'adresse' => '56 Avenue Houphouët-Boigny',
                'code_postal' => '01 BP 4567',
                'ville' => 'Yamoussoukro',
                'pays' => 'Côte d\'Ivoire',
                'telephone' => '+225 27 30 60 70 80',
                'email' => 'commercial@entreprise.com',
                'has_geofencing' => false,
                'geofencing_radius' => 0,
                'latitude' => 6.8276,
                'longitude' => -5.2893,
                'statut' => 'actif',
                'configuration' => [
                    'capacite_max' => '50',
                    'horaires_ouverture' => '08:00-17:00',
                ],
            ],
            [
                'nom' => 'Centre de Formation',
                'description' => 'Centre dédié à la formation des employés',
                'adresse' => '23 Boulevard de la Paix',
                'code_postal' => '10 BP 5678',
                'ville' => 'Bouaké',
                'pays' => 'Côte d\'Ivoire',
                'telephone' => '+225 27 31 35 40 45',
                'email' => 'formation@entreprise.com',
                'has_geofencing' => false,
                'geofencing_radius' => 0,
                'latitude' => 7.6906,
                'longitude' => -5.0303,
                'statut' => 'actif',
                'configuration' => [
                    'capacite_max' => '100',
                    'horaires_ouverture' => '08:30-16:30',
                ],
            ],
        ];
    }

    /**
     * Seed the application's database with example sites.
     */
    public function run()
    {
        // Cette méthode n'est pas utilisée directement
        // Nous utilisons plutôt la méthode createForEntreprise
    }

    /**
     * Crée des sites exemples pour une entreprise spécifique
     */
    public static function createForEntreprise(Entreprise $entreprise)
    {
        $sites = self::getSitesExemples();
        $created = [];
        $existants = 0;

        foreach ($sites as $site) {
            // Vérifier si un site similaire existe déjà pour cette entreprise
            $existant = Site::where('entreprise_id', $entreprise->id)
                ->where('nom', $site['nom'])
                ->first();
            
            if (!$existant) {
                $newSite = [
                    'nom' => $site['nom'],
                    'description' => $site['description'],
                    'adresse' => $site['adresse'],
                    'code_postal' => $site['code_postal'],
                    'ville' => $site['ville'],
                    'pays' => $site['pays'],
                    'telephone' => $site['telephone'],
                    'email' => $site['email'],
                    'has_geofencing' => $site['has_geofencing'],
                    'geofencing_radius' => $site['geofencing_radius'],
                    'latitude' => $site['latitude'],
                    'longitude' => $site['longitude'],
                    'entreprise_id' => $entreprise->id,
                    'statut' => $site['statut'],
                    'configuration' => $site['configuration'],
                ];
                
                $createdSite = Site::create($newSite);
                $created[] = $createdSite;
            } else {
                $existants++;
            }
        }

        return [
            'created' => $created,
            'existants' => $existants,
            'total' => count($sites)
        ];
    }
}
