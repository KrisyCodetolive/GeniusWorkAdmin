<?php

namespace Database\Seeders;

use App\Models\Entreprise;
use App\Models\Paie\ConfigurationPaie;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ConfigurationPaieExempleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Cette méthode ne sera pas utilisée directement
        // Utilisez plutôt la méthode createForEntreprise
    }

    /**
     * Crée des exemples de configurations de paie pour une entreprise spécifique
     * 
     * @param Entreprise $entreprise
     * @return array
     */
    public static function createForEntreprise(Entreprise $entreprise): array
    {
        // Vérifier si l'entreprise a déjà des configurations de paie
        $existingConfigs = ConfigurationPaie::where('entreprise_id', $entreprise->id)->count();
        
        // Si l'entreprise a déjà des configurations, retourner un message
        if ($existingConfigs > 0) {
            return [
                'created' => [],
                'message' => 'Votre entreprise possède déjà des configurations de paie. Vous pouvez les modifier ou en créer de nouvelles manuellement.'
            ];
        }

        // Configurations à créer
        $configurations = [
            [
                'nom' => 'Configuration standard',
                'description' => 'Configuration standard conforme à la législation ivoirienne',
                'est_defaut' => true,
                'smig' => 75000,
                'plafond_cnps' => 225000,
                'taux_cnps_employe' => 6.3,
                'taux_cnps_employeur' => 7.7,
                'taux_prestations_familiales' => 5.75,
                'taux_accident_travail' => 2.0,
                'taux_assurance_maladie' => 0.75,
                'abattement_igr' => 20.0,
                'baremes_igr' => [
                    ['min' => 0, 'max' => 25000, 'taux' => 0],
                    ['min' => 25001, 'max' => 45000, 'taux' => 10],
                    ['min' => 45001, 'max' => 80000, 'taux' => 15],
                    ['min' => 80001, 'max' => 180000, 'taux' => 20],
                    ['min' => 180001, 'max' => 0, 'taux' => 25],
                ],
                'parametres_indemnites' => [
                    ['nom' => 'Indemnité de transport', 'type' => 'montant_fixe', 'montant' => 30000, 'imposable' => false],
                    ['nom' => 'Indemnité de logement', 'type' => 'pourcentage', 'taux' => 15, 'imposable' => true],
                ],
                'parametres_primes' => [
                    ['nom' => 'Prime d\'ancienneté', 'type' => 'pourcentage', 'taux' => 5, 'imposable' => true],
                    ['nom' => 'Prime de rendement', 'type' => 'pourcentage', 'taux' => 10, 'imposable' => true],
                ],
            ],
            [
                'nom' => 'Configuration cadres supérieurs',
                'description' => 'Configuration adaptée aux cadres supérieurs avec des avantages spécifiques',
                'est_defaut' => false,
                'smig' => 75000,
                'plafond_cnps' => 225000,
                'taux_cnps_employe' => 6.3,
                'taux_cnps_employeur' => 7.7,
                'taux_prestations_familiales' => 5.75,
                'taux_accident_travail' => 2.0,
                'taux_assurance_maladie' => 0.75,
                'abattement_igr' => 20.0,
                'baremes_igr' => [
                    ['min' => 0, 'max' => 25000, 'taux' => 0],
                    ['min' => 25001, 'max' => 45000, 'taux' => 10],
                    ['min' => 45001, 'max' => 80000, 'taux' => 15],
                    ['min' => 80001, 'max' => 180000, 'taux' => 20],
                    ['min' => 180001, 'max' => 0, 'taux' => 25],
                ],
                'parametres_indemnites' => [
                    ['nom' => 'Indemnité de transport', 'type' => 'montant_fixe', 'montant' => 50000, 'imposable' => false],
                    ['nom' => 'Indemnité de logement', 'type' => 'pourcentage', 'taux' => 20, 'imposable' => true],
                    ['nom' => 'Indemnité de représentation', 'type' => 'montant_fixe', 'montant' => 100000, 'imposable' => true],
                ],
                'parametres_primes' => [
                    ['nom' => 'Prime d\'ancienneté', 'type' => 'pourcentage', 'taux' => 8, 'imposable' => true],
                    ['nom' => 'Prime de rendement', 'type' => 'pourcentage', 'taux' => 15, 'imposable' => true],
                    ['nom' => 'Prime de responsabilité', 'type' => 'pourcentage', 'taux' => 10, 'imposable' => true],
                ],
            ],
            [
                'nom' => 'Configuration stagiaires',
                'description' => 'Configuration adaptée aux stagiaires et apprentis',
                'est_defaut' => false,
                'smig' => 50000, // SMIG réduit pour les stagiaires
                'plafond_cnps' => 225000,
                'taux_cnps_employe' => 6.3,
                'taux_cnps_employeur' => 7.7,
                'taux_prestations_familiales' => 5.75,
                'taux_accident_travail' => 2.0,
                'taux_assurance_maladie' => 0.75,
                'abattement_igr' => 20.0,
                'baremes_igr' => [
                    ['min' => 0, 'max' => 25000, 'taux' => 0],
                    ['min' => 25001, 'max' => 45000, 'taux' => 10],
                    ['min' => 45001, 'max' => 80000, 'taux' => 15],
                    ['min' => 80001, 'max' => 180000, 'taux' => 20],
                    ['min' => 180001, 'max' => 0, 'taux' => 25],
                ],
                'parametres_indemnites' => [
                    ['nom' => 'Indemnité de transport', 'type' => 'montant_fixe', 'montant' => 20000, 'imposable' => false],
                ],
                'parametres_primes' => [],
            ],
        ];

        $created = [];

        // Créer les configurations
        foreach ($configurations as $configData) {
            $config = new ConfigurationPaie();
            $config->id = (string) Str::uuid();
            $config->entreprise_id = $entreprise->id;
            $config->nom = $configData['nom'];
            $config->description = $configData['description'];
            $config->est_defaut = $configData['est_defaut'];
            $config->smig = $configData['smig'];
            $config->plafond_cnps = $configData['plafond_cnps'];
            $config->taux_cnps_employe = $configData['taux_cnps_employe'];
            $config->taux_cnps_employeur = $configData['taux_cnps_employeur'];
            $config->taux_prestations_familiales = $configData['taux_prestations_familiales'];
            $config->taux_accident_travail = $configData['taux_accident_travail'];
            $config->taux_assurance_maladie = $configData['taux_assurance_maladie'];
            $config->abattement_igr = $configData['abattement_igr'];
            $config->baremes_igr = $configData['baremes_igr'];
            $config->parametres_indemnites = $configData['parametres_indemnites'];
            $config->parametres_primes = $configData['parametres_primes'];
            $config->save();

            $created[] = $config;
        }

        return [
            'created' => $created,
            'message' => count($created) . ' configuration(s) de paie exemple(s) ont été créées avec succès.'
        ];
    }
}
