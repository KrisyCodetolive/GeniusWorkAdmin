<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Entreprise;
use App\Models\Employeur;
use App\Models\Site;
use App\Models\Presence;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;


class PresenceExempleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Cette méthode n'est pas utilisée directement
        // Utilisez createForEntreprise à la place
    }

    /**
     * Crée des exemples de présences pour une entreprise spécifique
     * 
     * @param Entreprise $entreprise L'entreprise pour laquelle créer des exemples
     * @return array Résultat de l'opération
     */
    public static function createForEntreprise(Entreprise $entreprise): array
    {
        $result = [
            'created' => [],
            'skipped' => [],
            'message' => ''
        ];

        // Vérifier si l'entreprise a des sites
        $sites = $entreprise->sites;
        if ($sites->isEmpty()) {
            $result['message'] = 'Aucun site trouvé pour cette entreprise. Veuillez d\'abord créer des sites.';
            return $result;
        }

        // Vérifier si l'entreprise a des employés
        $employeurs = $entreprise->employeurs;
        if ($employeurs->isEmpty()) {
            $result['message'] = 'Aucun employé trouvé pour cette entreprise. Veuillez d\'abord créer des employés.';
            return $result;
        }

        // Générer des présences sur les 7 derniers jours
        $today = Carbon::today();
        $startDate = $today->copy()->subDays(7);

        foreach ($employeurs as $employeur) {
            // Pour chaque employé, générer des présences sur différents sites
            for ($day = 0; $day < 7; $day++) {
                $date = $startDate->copy()->addDays($day);
                
                // Sauter les weekends (samedi et dimanche)
                if ($date->isWeekend()) {
                    continue;
                }
                
                // Choisir un site aléatoire pour cette journée
                $site = $sites->random();
                
                // Générer une présence avec entrée et sortie
                $entryTime = $date->copy()->setHour(rand(8, 9))->setMinute(rand(0, 59));
                $exitTime = $date->copy()->setHour(rand(16, 18))->setMinute(rand(0, 59));
                
                // Statuts possibles
                $statuts = ['present', 'retard', 'present', 'present', 'present']; // Plus de chances d'être présent
                $statut = $statuts[array_rand($statuts)];
                
                // Si retard, ajuster l'heure d'entrée
                if ($statut === 'retard') {
                    $entryTime->addHours(1);
                }
                
                // Créer la présence
                $presence = Presence::create([
                    'employeur_id' => $employeur->id,
                    'site_id' => $site->id,
                    'date_heure_entree' => $entryTime,
                    'date_heure_sortie' => $exitTime,
                    'statut' => $statut,
                    'commentaire' => 'Présence exemple générée automatiquement',
                    'duree_effective' => $exitTime->diffInMinutes($entryTime),
                    'retard' => $statut === 'retard' ? rand(15, 60) : 0,
                    'statut_validation' => 'approuve',
                    'validateur_id' => Auth::id(),
                    'date_validation' => Carbon::now(),
                    'adresse_ip_entree' => '127.0.0.1',
                    'adresse_ip_sortie' => '127.0.0.1',
                    'appareil_entree' => 'Générateur d\'exemples',
                    'appareil_sortie' => 'Générateur d\'exemples',
                ]);
                
                $result['created'][] = [
                    'id' => $presence->id,
                    'employeur' => $employeur->nom . ' ' . $employeur->prenom,
                    'site' => $site->nom,
                    'date' => $date->format('Y-m-d')
                ];
            }
        }
        
        // Message de résultat
        $nbCreated = count($result['created']);
        if ($nbCreated > 0) {
            $result['message'] = "{$nbCreated} présences exemples ont été générées avec succès pour {$employeurs->count()} employés sur {$sites->count()} sites différents.";
        } else {
            $result['message'] = "Aucune présence exemple n'a pu être générée.";
        }
        
        return $result;
    }
}
