<?php

namespace App\Filament\Resources\AppareilBiometriqueResource\Pages;

use App\Filament\Resources\AppareilBiometriqueResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use App\Services\Biometrique\AppareilBiometriqueService;
use App\Services\Biometrique\AppareilBiometriqueStatsService;
use Filament\Notifications\Notification;

class ViewAppareilBiometrique extends ViewRecord
{
    protected static string $resource = AppareilBiometriqueResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\Action::make('tester_connexion')
                ->label('Tester la connexion')
                ->icon('heroicon-o-signal')
                ->color('success')
                ->action(function () {
                    $appareil = $this->record;
                    $appareilService = app(AppareilBiometriqueService::class);
                    
                    $success = $appareilService->testerConnexion($appareil);
                    
                    if ($success) {
                        Notification::make()
                            ->title('Connexion réussie')
                            ->success()
                            ->send();
                    } else {
                        Notification::make()
                            ->title('Échec de connexion')
                            ->danger()
                            ->send();
                    }
                }),
            Actions\Action::make('synchroniser')
                ->label('Synchroniser')
                ->icon('heroicon-o-arrow-path')
                ->color('warning')
                ->action(function () {
                    $appareil = $this->record;
                    $appareilService = app(AppareilBiometriqueService::class);
                    
                    try {
                        $appareilService->synchroniserDonnees($appareil);
                        
                        Notification::make()
                            ->title('Synchronisation réussie')
                            ->success()
                            ->send();
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Échec de synchronisation')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                })
                ->visible(fn () => $this->record->estActif()),
        ];
    }
}
