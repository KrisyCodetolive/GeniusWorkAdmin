<?php

namespace App\Filament\Resources\MonEntrepriseResource\Pages;

use App\Filament\Resources\MonEntrepriseResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

class EditMonEntreprise extends EditRecord
{
    protected static string $resource = MonEntrepriseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->getRecord()]);
    }

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Entreprise mise à jour')
            ->body('Les informations de votre entreprise ont été mises à jour avec succès.');
    }

    public function mount(int|string $record): void
    {
        $user = Auth::user();
        
        // Vérifier si l'utilisateur a accès à cette entreprise
        if ($user && !$user->hasRole(['SuperAdmin', 'Support']) && $user->entreprise_id != $record) {
            $this->redirect(MonEntrepriseResource::getUrl('edit', ['record' => $user->entreprise_id]));
            return;
        }
        
        parent::mount($record);
    }
}
