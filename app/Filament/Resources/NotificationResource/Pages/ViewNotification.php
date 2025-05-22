<?php

namespace App\Filament\Resources\NotificationResource\Pages;

use App\Filament\Resources\NotificationResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewNotification extends ViewRecord
{
    protected static string $resource = NotificationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\Action::make('marquerCommeLue')
                ->label('Marquer comme lue')
                ->icon('heroicon-o-check')
                ->color('success')
                ->visible(fn () => !$this->record->estLue())
                ->action(function () {
                    $this->record->marquerCommeLue();
                    $this->notify('success', 'La notification a été marquée comme lue');
                }),
            Actions\Action::make('marquerCommeEnvoyee')
                ->label('Marquer comme envoyée')
                ->icon('heroicon-o-paper-airplane')
                ->color('primary')
                ->visible(fn () => $this->record->statut === 'en_attente')
                ->action(function () {
                    $this->record->marquerCommeEnvoyee();
                    $this->notify('success', 'La notification a été marquée comme envoyée');
                }),
            Actions\Action::make('renvoyer')
                ->label('Renvoyer')
                ->icon('heroicon-o-arrow-path')
                ->color('warning')
                ->visible(fn () => $this->record->statut === 'echec')
                ->action(function () {
                    $this->record->update([
                        'statut' => 'en_attente',
                        'erreur' => null,
                        'prochaine_tentative' => null
                    ]);
                    $this->notify('success', 'La notification a été mise en file d\'attente pour un nouvel envoi');
                }),
        ];
    }
}
