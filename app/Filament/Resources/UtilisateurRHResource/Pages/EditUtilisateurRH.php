<?php

namespace App\Filament\Resources\UtilisateurRHResource\Pages;

use App\Filament\Resources\UtilisateurRHResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditUtilisateurRH extends EditRecord
{
    protected static string $resource = UtilisateurRHResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->icon('heroicon-o-trash')
                ->modalHeading('Supprimer cet utilisateur RH')
                ->modalDescription('Êtes-vous sûr de vouloir supprimer cet utilisateur RH ? Cette action est irréversible.')
                ->modalSubmitActionLabel('Oui, supprimer'),
        ];
    }
    
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
    
    protected function getSavedNotificationTitle(): ?string
    {
        return "Les informations de l'utilisateur RH ont été mises à jour";
    }
    
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        // Conserver l'entreprise_id existant
        $data['entreprise_id'] = $record->entreprise_id;
        
        $record->update($data);
        
        return $record;
    }
}
