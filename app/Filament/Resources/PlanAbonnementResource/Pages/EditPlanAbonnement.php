<?php

namespace App\Filament\Resources\PlanAbonnementResource\Pages;

use App\Filament\Resources\PlanAbonnementResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditPlanAbonnement extends EditRecord
{
    protected static string $resource = PlanAbonnementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->requiresConfirmation()
                ->modalDescription('Êtes-vous sûr de vouloir supprimer ce plan ? Cette action est irréversible.'),
            Actions\Action::make('duplicate')
                ->icon('heroicon-o-document-duplicate')
                ->action(function () {
                    $newPlan = $this->record->replicate();
                    $newPlan->nom = $newPlan->nom . ' (copie)';
                    $newPlan->save();

                    Notification::make()
                        ->success()
                        ->title('Plan dupliqué')
                        ->body('Le plan a été dupliqué avec succès.')
                        ->send();

                    return redirect()->route('filament.admin.resources.plan-abonnements.edit', ['record' => $newPlan]);
                }),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Plan modifié')
            ->body('Les modifications ont été enregistrées avec succès.');
    }

    protected function beforeSave(): void
    {
        // Validation supplémentaire avant la sauvegarde
        $data = $this->data;
        
        // Vérifier si le prix a été modifié
        if ($this->record->prix_mensuel !== $data['prix_mensuel'] || $this->record->prix_annuel !== $data['prix_annuel']) {
            // Logique pour gérer le changement de prix
            // Par exemple, notifier les administrateurs
            Notification::make()
                ->warning()
                ->title('Modification de prix')
                ->body('Le prix du plan a été modifié. Les abonnements existants ne seront pas affectés.')
                ->persistent()
                ->send();
        }
    }

    protected function afterSave(): void
    {
        // Actions après la sauvegarde
        // Par exemple, mettre à jour les statistiques ou envoyer des notifications
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Modifier les données si nécessaire avant la sauvegarde
        return $data;
    }
}
