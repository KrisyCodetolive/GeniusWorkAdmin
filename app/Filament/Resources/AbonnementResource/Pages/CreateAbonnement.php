<?php

namespace App\Filament\Resources\AbonnementResource\Pages;

use App\Filament\Resources\AbonnementResource;
use Filament\Resources\Pages\CreateRecord;
use App\Services\AbonnementService;
use App\Models\Entreprise;
use App\Models\PlanAbonnement;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;

class CreateAbonnement extends CreateRecord
{
    protected static string $resource = AbonnementResource::class;
    
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
    
    protected function handleRecordCreation(array $data): Model
    {
        // Utiliser le service d'abonnement pour créer l'abonnement
        $entreprise = Entreprise::findOrFail($data['entreprise_id']);
        $planAbonnement = PlanAbonnement::findOrFail($data['plan_abonnement_id']);
        
        $abonnementService = app(AbonnementService::class);
        $abonnement = $abonnementService->creerAbonnement($entreprise, $planAbonnement, $data);
        
        Notification::make()
            ->title('Abonnement créé avec succès')
            ->body('L\'abonnement a été créé et les autres abonnements actifs de cette entreprise ont été désactivés.')
            ->success()
            ->send();
            
        return $abonnement;
    }
    
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Si l'utilisateur n'est pas un super admin, utiliser son entreprise
        if (!auth()->user()->isSuperAdmin() && empty($data['entreprise_id'])) {
            $data['entreprise_id'] = auth()->user()->entreprise_id;
        }
        
        return $data;
    }
}
