<?php

namespace App\Filament\Resources\PolitiqueResource\Pages;

use App\Filament\Resources\PolitiqueResource;
use App\Models\Politique;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;

class CreatePolitique extends CreateRecord
{
    protected static string $resource = PolitiqueResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
    
    protected function handleRecordCreation(array $data): Model
    {
        $user = auth()->user();
        
        // Si l'utilisateur n'est ni SuperAdmin ni Support
        if (!$user->isSuperAdmin() && !$user->isSupport()) {
            $entrepriseId = $user->entreprise_id;
            
            // Vérifier si son entreprise a déjà une politique
            $politiqueExiste = Politique::where('entreprise_id', $entrepriseId)->exists();
            
            if ($politiqueExiste) {
                Notification::make()
                    ->title('Création impossible')
                    ->body('Votre entreprise possède déjà une politique. Vous ne pouvez pas en créer une nouvelle.')
                    ->danger()
                    ->send();
                
                $this->redirect($this->getResource()::getUrl('index'));
                return new Politique(); // Retourner un modèle vide pour éviter les erreurs
            }
            
            // Définir automatiquement l'entreprise_id pour les utilisateurs normaux
            $data['entreprise_id'] = $entrepriseId;
        }
        
        return parent::handleRecordCreation($data);
    }
}
