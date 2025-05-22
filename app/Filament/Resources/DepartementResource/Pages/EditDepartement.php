<?php

namespace App\Filament\Resources\DepartementResource\Pages;

use App\Filament\Resources\DepartementResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDepartement extends EditRecord
{
    protected static string $resource = DepartementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make()
                ->before(function ($action, $record) {
                    // Vérifier si le département peut être supprimé
                    if (!$record->peutEtreSupprime()) {
                        $action->cancel();
                        $action->failureNotification()?->title('Impossible de supprimer ce département')
                            ->body('Ce département contient des employés ou des sous-départements. Veuillez les réaffecter avant de supprimer ce département.');
                    }
                }),
            Actions\Action::make('arborescence')
                ->label('Voir l\'arborescence')
                ->icon('heroicon-o-chart-bar')
                ->url(fn () => $this->getResource()::getUrl('arborescence', ['record' => $this->record])),
        ];
    }

    protected function afterSave(): void
    {
        // Recalculer le niveau hiérarchique après la modification
        $this->record->calculerEtMettreAJourNiveau();
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $user = auth()->user();
        
        // Si l'utilisateur n'est pas SuperAdmin ou Support, on s'assure qu'il ne peut pas modifier l'entreprise
        if (!$user->isSuperAdmin() && !$user->isSupport()) {
            $data['entreprise_id'] = $this->record->entreprise_id;
        }
        
        // Si le code est vide et que le nom a changé, générer un nouveau code
        if (empty($data['code']) && $this->record->nom !== $data['nom']) {
            $data['code'] = $this->record->genererCode($data['nom'], $data['filiale_id']);
        }

        return $data;
    }
}
