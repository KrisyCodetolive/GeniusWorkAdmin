<?php

namespace App\Filament\Resources\EmployeurResource\Pages;

use App\Filament\Resources\EmployeurResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use App\Services\EmployeurCarteService;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Storage;

class ViewEmployeur extends ViewRecord
{
    protected static string $resource = EmployeurResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\Action::make('telechargerCarte')
                ->label('Télécharger Carte')
                ->icon('heroicon-o-identification')
                ->color('success')
                ->action(function () {
                    $record = $this->getRecord();
                    $carteService = app(EmployeurCarteService::class);
                    $cartePath = $carteService->genererCarte($record);
                    
                    $url = Storage::disk('public')->url($cartePath);
                    
                    Notification::make()
                        ->title('Carte générée avec succès')
                        ->success()
                        ->actions([
                            \Filament\Notifications\Actions\Action::make('download')
                                ->label('Télécharger')
                                ->url($url)
                                ->openUrlInNewTab(),
                        ])
                        ->send();
                        
                    return redirect($url);
                }),
            Actions\Action::make('rotateQRCode')
                ->label('Régénérer QR Code')
                ->icon('heroicon-o-qr-code')
                ->color('warning')
                ->action(function () {
                    $record = $this->getRecord();
                    $record->rotateQRCode();
                    $record->save();
                    
                    Notification::make()
                        ->title('QR Code régénéré avec succès')
                        ->success()
                        ->send();
                })
                ->requiresConfirmation(),
            Actions\Action::make('desactiverEmployeur')
                ->label('Désactiver Employeur')
                ->icon('heroicon-o-user-minus')
                ->color('danger')
                ->visible(fn ($record) => $record->estActif())
                ->action(function () {
                    $record = $this->getRecord();
                    $record->statut = 'inactif';
                    $record->save();
                    
                    // Désactiver également le QR Code
                    $record->deactivateQRCode();
                    
                    Notification::make()
                        ->title('Employeur désactivé avec succès')
                        ->success()
                        ->send();
                })
                ->requiresConfirmation()
                ->modalHeading('Désactiver l\'employeur')
                ->modalDescription('Êtes-vous sûr de vouloir désactiver cet employeur ? Cette action désactivera également son QR Code.')
                ->modalSubmitActionLabel('Oui, désactiver'),
        ];
    }
}
