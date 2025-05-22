<?php

namespace App\Filament\Resources\SupplementaireResource\Pages;

use App\Filament\Resources\SupplementaireResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSupplementaire extends EditRecord
{
    protected static string $resource = SupplementaireResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\Action::make('valider')
                ->label('Approuver')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->visible(fn () => $this->record->statut === 'en_attente')
                ->action(function () {
                    $this->record->valider(auth()->user());
                    $this->redirect($this->getResource()::getUrl('index'));
                }),
            Actions\Action::make('rejeter')
                ->label('Rejeter')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->visible(fn () => $this->record->statut === 'en_attente')
                ->form([
                    \Filament\Forms\Components\Textarea::make('commentaire')
                        ->label('Motif de rejet')
                        ->required(),
                ])
                ->action(function (array $data) {
                    $this->record->rejeter(auth()->user(), $data['commentaire']);
                    $this->redirect($this->getResource()::getUrl('index'));
                }),
            Actions\Action::make('calculer')
                ->label('Calculer montant')
                ->icon('heroicon-o-calculator')
                ->color('gray')
                ->action(function () {
                    $this->record->calculerMontant();
                    $this->refreshFormData(['montant']);
                }),
        ];
    }
    
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
