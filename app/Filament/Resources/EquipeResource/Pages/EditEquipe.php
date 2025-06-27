<?php

namespace App\Filament\Resources\EquipeResource\Pages;

use App\Filament\Resources\EquipeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditEquipe extends EditRecord
{
    protected static string $resource = EquipeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\Action::make('synchroniserHoraires')
                ->label('Synchroniser les horaires')
                ->icon('heroicon-o-clock')
                ->action(function () {
                    $this->record->synchroniserHoraires();
                    
                    Notification::make()
                        ->title('Horaires synchronisés')
                        ->body('Les horaires ont été synchronisés pour tous les membres de l\'équipe.')
                        ->success()
                        ->send();
                })
                ->requiresConfirmation()
                ->modalHeading('Synchroniser les horaires')
                ->modalDescription('Cette action va appliquer les plages horaires de l\'équipe à tous ses membres actifs.')
                ->modalSubmitActionLabel('Synchroniser')
                ->color('success'),
        ];
    }
}
