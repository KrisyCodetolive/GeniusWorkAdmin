<?php

namespace App\Filament\Resources\EmployeurResource\Pages;

use App\Filament\Resources\EmployeurResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditEmployeur extends EditRecord
{
    protected static string $resource = EmployeurResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\Action::make('rotateQRCode')
                ->label('Régénérer QR Code')
                ->icon('heroicon-o-qr-code')
                ->color('warning')
                ->action(function () {
                    $this->record->rotateQRCode();
                    $this->record->save();
                    Notification::make()
                    ->title('QR Code régénéré')
                    ->body('Le QR Code a été régénéré avec succès.')
                    ->success()
                    ->send();
            
                })
                ->requiresConfirmation(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $user = auth()->user();
        
        // Si l'utilisateur n'est pas SuperAdmin ou Support, on s'assure qu'il ne peut pas modifier l'entreprise
        if (!$user->isSuperAdmin() && !$user->isSupport()) {
            $data['entreprise_id'] = $this->record->entreprise_id;
        }
        
        return $data;
    }
}
