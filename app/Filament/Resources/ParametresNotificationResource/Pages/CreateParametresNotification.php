<?php

namespace App\Filament\Resources\ParametresNotificationResource\Pages;

use App\Filament\Resources\ParametresNotificationResource;
use App\Models\ParametresNotification;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;

class CreateParametresNotification extends CreateRecord
{
    protected static string $resource = ParametresNotificationResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
    
    protected function handleRecordCreation(array $data): Model
    {
        $user = auth()->user();
        
        // Si l'utilisateur n'est ni SuperAdmin ni Support, vérifier si son entreprise a déjà des paramètres
        if (!$user->isSuperAdmin() && !$user->isSupport()) {
            $entrepriseId = $user->entreprise_id;
            $parametresExistent = ParametresNotification::where('entreprise_id', $entrepriseId)->exists();
            
            if ($parametresExistent) {
                Notification::make()
                    ->title('Création impossible')
                    ->body('Votre entreprise possède déjà des paramètres de notification. Vous ne pouvez pas en créer de nouveaux.')
                    ->danger()
                    ->send();
                
                $this->redirect($this->getResource()::getUrl('index'));
                return new ParametresNotification(); // Retourner un modèle vide pour éviter les erreurs
            }
        }
        
        return parent::handleRecordCreation($data);
    }
    
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = auth()->user();
        
        // Si l'utilisateur n'est pas SuperAdmin ou Support, utiliser son entreprise_id
        if (!$user->isSuperAdmin() && !$user->isSupport()) {
            $data['entreprise_id'] = $user->entreprise_id;
        }
        
        return $data;
    }
}
