<?php

namespace App\Filament\Resources\SecurityKeyResource\Pages;

use App\Filament\Resources\SecurityKeyResource;
use App\Models\SecurityKey;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditSecurityKey extends EditRecord
{
    protected static string $resource = SecurityKeyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            
            Actions\Action::make('activate')
                ->label('Activer cette clé')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->action(function () {
                    // Désactiver toutes les autres clés
                    SecurityKey::where('id', '!=', $this->record->id)
                        ->where('is_active', true)
                        ->update(['is_active' => false]);
                    
                    // Activer cette clé
                    $this->record->update(['is_active' => true]);
                    
                    $this->notify('success', 'Clé activée avec succès');
                })
                ->visible(fn () => !$this->record->is_active)
                ->requiresConfirmation()
                ->modalHeading('Activer cette clé')
                ->modalDescription('Êtes-vous sûr de vouloir activer cette clé ? Toutes les autres clés actives seront désactivées.')
                ->modalSubmitActionLabel('Activer'),
                
            Actions\Action::make('deactivate')
                ->label('Désactiver cette clé')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->action(function () {
                    $this->record->update(['is_active' => false]);
                    $this->notify('success', 'Clé désactivée avec succès');
                })
                ->visible(fn () => $this->record->is_active)
                ->requiresConfirmation()
                ->modalHeading('Désactiver cette clé')
                ->modalDescription('Êtes-vous sûr de vouloir désactiver cette clé ? Les QR codes générés avec cette clé ne seront plus valides.')
                ->modalSubmitActionLabel('Désactiver'),
        ];
    }
    
    protected function mutateFormData(array $data): array
    {
        // Si la clé devient active, désactiver toutes les autres
        if (isset($data['is_active']) && $data['is_active'] && !$this->record->is_active) {
            SecurityKey::where('id', '!=', $this->record->id)
                ->where('is_active', true)
                ->update(['is_active' => false]);
        }
        
        return $data;
    }
    
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
    
    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Clé de sécurité mise à jour')
            ->body('La clé de sécurité a été mise à jour avec succès.');
    }
}
