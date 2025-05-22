<?php

namespace App\Filament\Resources\ParametresNotificationResource\Pages;

use App\Filament\Resources\ParametresNotificationResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditParametresNotification extends EditRecord
{
    protected static string $resource = ParametresNotificationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
            Actions\Action::make('testerNotification')
                ->label('Tester les notifications')
                ->icon('heroicon-o-paper-airplane')
                ->color('success')
                ->action(function () {
                    // Logique pour tester l'envoi d'une notification de test
                    $parametres = $this->record;
                    
                    // Créer une notification de test
                    $notification = new \App\Models\Notification([
                        'user_id' => auth()->id(),
                        'entreprise_id' => $parametres->entreprise_id,
                        'type' => 'test',
                        'titre' => 'Notification de test',
                        'message' => 'Ceci est une notification de test envoyée depuis les paramètres de notification.',
                        'priorite' => 'normale',
                        'statut' => 'envoye',
                        'canal' => implode(',', $parametres->canauxActifs()),
                        'date_envoi' => now(),
                    ]);
                    
                    $notification->save();
                    
                    $this->notify('success', 'Une notification de test a été envoyée.');
                }),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
