<?php

namespace App\Filament\Resources\FacturationResource\Pages;

use App\Filament\Resources\FacturationResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Notifications\Notification;

class ViewFacturation extends ViewRecord
{
    protected static string $resource = FacturationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\DeleteAction::make()
                ->visible(fn () => auth()->user()->isSuperAdmin()),
            Actions\Action::make('marquer_paye')
                ->label('Marquer comme payé')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->visible(fn () => $this->record->statut_paiement !== 'paye' && auth()->user()->isSuperAdmin())
                ->action(function () {
                    $this->record->update([
                        'statut_paiement' => 'paye',
                    ]);
                    
                    Notification::make()
                        ->title('Facturation marquée comme payée')
                        ->success()
                        ->send();
                        
                    $this->redirect($this->getResource()::getUrl('view', ['record' => $this->record]));
                }),
            Actions\Action::make('generer_paiement')
                ->label('Générer un paiement')
                ->icon('heroicon-o-currency-dollar')
                ->color('primary')
                ->requiresConfirmation()
                ->visible(fn () => $this->record->statut_paiement !== 'paye')
                ->action(function () {
                    // Rediriger vers la page de choix de méthode de paiement
                    return redirect()->route('paiements.choisir-methode', ['facturationId' => $this->record->id]);
                }),
            Actions\Action::make('telecharger_facture')
                ->label('Télécharger la facture')
                ->icon('heroicon-o-document-arrow-down')
                ->color('gray')
                ->url(fn () => route('facture.telecharger', ['facturation' => $this->record]))
                ->openUrlInNewTab(),
        ];
    }
}
