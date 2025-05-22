<?php

namespace App\Filament\Resources\PlageHoraireBaseResource\Pages;

use App\Filament\Resources\PlageHoraireBaseResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditPlageHoraireBase extends EditRecord
{
    protected static string $resource = PlageHoraireBaseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Si jours_travail n'est pas défini ou est vide, initialiser avec des valeurs par défaut
        if (!isset($data['jours_travail']) || empty($data['jours_travail'])) {
            $data['jours_travail'] = $this->getDefaultJoursTravail($data);
        } else if (is_string($data['jours_travail'])) {
            // Si jours_travail est une chaîne JSON, la convertir en tableau
            $data['jours_travail'] = json_decode($data['jours_travail'], true) ?? [];
            
            // Vérifier si les heures sont définies pour chaque jour
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

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // S'assurer que jours_travail est correctement formaté
        if (isset($data['jours_travail']) && !empty($data['jours_travail'])) {
            // Nettoyer les données des jours de travail
            foreach ($data['jours_travail'] as $key => $jour) {
                // S'assurer que les champs requis sont présents
                $data['jours_travail'][$key]['jour_semaine'] = $jour['jour_semaine'] ?? '';
                $data['jours_travail'][$key]['est_travaille'] = $jour['est_travaille'] ?? true;
                $data['jours_travail'][$key]['est_ferie'] = $jour['est_ferie'] ?? false;
                
                // Utiliser les heures par défaut si non définies
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
