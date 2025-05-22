<?php

namespace App\Filament\Resources\SoldeCongeResource\Pages;

use App\Filament\Resources\SoldeCongeResource;
use App\Traits\HasEntrepriseScope;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateSoldeConge extends CreateRecord
{
    use HasEntrepriseScope;
    
    protected static string $resource = SoldeCongeResource::class;
    
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = auth()->user();
        
        // Si l'utilisateur n'est pas SuperAdmin ou Support et qu'un user_id n'est pas défini,
        // on utilise l'id de l'utilisateur actuel
        if (!$user->isSuperAdmin() && !$user->isSupport() && !isset($data['user_id'])) {
            $data['user_id'] = $user->id;
        }
        
        // Si l'année n'est pas définie, utiliser l'année en cours
        if (!isset($data['annee'])) {
            $data['annee'] = date('Y');
        }
        
        // Définir la date de dernière mise à jour
        $data['date_derniere_maj'] = now();
        
        return $data;
    }
    
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
