<?php

namespace App\Filament\Resources\PlanAbonnementResource\Pages;

use App\Filament\Resources\PlanAbonnementResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

class CreatePlanAbonnement extends CreateRecord
{
    protected static string $resource = PlanAbonnementResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Plan créé')
            ->body('Le plan d\'abonnement a été créé avec succès.');
    }

    protected function beforeCreate(): void
    {
        // Validation supplémentaire si nécessaire
        $data = $this->data;
        
        // Vérifier que le prix est cohérent avec la période
        if ($data['periode_facturation'] === 'annuel' && $data['prix_annuel'] < 100) {
            Notification::make()
                ->warning()
                ->title('Attention')
                ->body('Le prix annuel semble très bas. Veuillez vérifier.')
                ->persistent()
                ->send();
        }
    }

    protected function afterCreate(): void
    {
        // Actions après création
        // Par exemple, créer des enregistrements associés ou envoyer des notifications
    }
}
