<?php

namespace App\Filament\Resources\EntrepriseResource\Pages;

use App\Filament\Resources\EntrepriseResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditEnterprise extends EditRecord
{
    protected static string $resource = EntrepriseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->before(function () {
                    // Vérifier si l'entreprise a des employés
                    if ($this->record->employeurs()->count() > 0) {
                        Notification::make()
                            ->title('Suppression impossible')
                            ->body('Cette entreprise a des employés. Veuillez d\'abord supprimer tous les employés.')
                            ->danger()
                            ->send();

                        $this->halt();
                    }
                }),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Entreprise mise à jour avec succès';
    }
}
