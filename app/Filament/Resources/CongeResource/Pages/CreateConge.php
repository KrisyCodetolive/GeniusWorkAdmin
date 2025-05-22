<?php

namespace App\Filament\Resources\CongeResource\Pages;

use App\Filament\Resources\CongeResource;
use App\Traits\HasEntrepriseScope;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Carbon\Carbon;

class CreateConge extends CreateRecord
{
    use HasEntrepriseScope;
    
    protected static string $resource = CongeResource::class;
    
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = auth()->user();
        
        // Si l'utilisateur n'est pas SuperAdmin, Support ou Admin, définir l'employeur comme l'utilisateur actuel
        if (!$user->isSuperAdmin() && !$user->isSupport() && !$user->isAdmin()) {
            // Récupérer l'employeur associé à l'utilisateur actuel
            $data['employeur_id'] = $user->employeur->id ?? null;
        }
        
        // Définir l'utilisateur qui a créé la demande
        $data['created_by'] = $user->id;
        
        // Calculer la durée en jours ouvrables si date_debut et date_fin sont définies
        if (isset($data['date_debut']) && isset($data['date_fin']) && (!isset($data['duree_jours']) || empty($data['duree_jours']))) {
            $debut = Carbon::parse($data['date_debut']);
            $fin = Carbon::parse($data['date_fin']);
            
            // Calculer les jours ouvrables
            $joursOuvrables = 0;
            $date = clone $debut;
            
            while ($date->lte($fin)) {
                if (!in_array($date->dayOfWeek, [0, 6])) { // Exclure samedi et dimanche
                    $joursOuvrables++;
                }
                $date->addDay();
            }
            
            $data['duree_jours'] = $joursOuvrables;
        }
        
        // S'assurer que duree_jours a une valeur par défaut
        if (!isset($data['duree_jours']) || empty($data['duree_jours'])) {
            $data['duree_jours'] = 1; // Valeur par défaut
        }
        
        return $data;
    }
    
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
