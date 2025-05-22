<?php

namespace App\Filament\Resources\MethodePointageResource\Pages;

use App\Filament\Resources\MethodePointageResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMethodePointage extends EditRecord
{
    protected static string $resource = MethodePointageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\Action::make('activer')
                ->label('Activer')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->visible(fn ($record) => $record->statut === 'inactif')
                ->action(function () {
                    $this->record->update(['statut' => 'actif']);
                    $this->notify('success', 'La méthode de pointage a été activée');
                }),
            Actions\Action::make('desactiver')
                ->label('Désactiver')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->visible(fn ($record) => $record->statut === 'actif')
                ->action(function () {
                    $this->record->update(['statut' => 'inactif']);
                    $this->notify('success', 'La méthode de pointage a été désactivée');
                }),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
