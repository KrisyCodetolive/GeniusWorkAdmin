<?php

namespace App\Filament\Resources\PlageHoraireBaseResource\Pages;

use App\Filament\Resources\PlageHoraireBaseResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreatePlageHoraireBase extends CreateRecord
{
    protected static string $resource = PlageHoraireBaseResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Ajouter l'entreprise_id de l'utilisateur connecté si non défini
        if (!isset($data['entreprise_id']) && auth()->check()) {
            $data['entreprise_id'] = auth()->user()->entreprise_id;
        }

        // Initialiser les jours de travail si non définis
        if (!isset($data['jours_travail']) || empty($data['jours_travail'])) {
            $data['jours_travail'] = $this->getDefaultJoursTravail($data);
        } else {
            // Si les jours de travail sont définis mais que les heures sont nulles, les remplir avec les valeurs par défaut
            foreach ($data['jours_travail'] as $key => $jour) {
                if (empty($jour['heure_debut']) && isset($data['heure_debut'])) {
                    $data['jours_travail'][$key]['heure_debut'] = $data['heure_debut'];
                }
                if (empty($jour['heure_fin']) && isset($data['heure_fin'])) {
                    $data['jours_travail'][$key]['heure_fin'] = $data['heure_fin'];
                }
            }
        }

        return $data;
    }

    /**
     * Génère les jours de travail par défaut (lundi à vendredi)
     */
    protected function getDefaultJoursTravail(array $data = []): array
    {
        $joursSemaine = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche'];
        $jours = [];

        foreach ($joursSemaine as $jour) {
            $estTravaille = in_array($jour, ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi']);
            
            $jours[] = [
                'jour_semaine' => $jour,
                'est_travaille' => $estTravaille,
                'est_ferie' => false,
                'heure_debut' => $data['heure_debut'] ?? null,
                'heure_fin' => $data['heure_fin'] ?? null,
            ];
        }

        return $jours;
    }
}
