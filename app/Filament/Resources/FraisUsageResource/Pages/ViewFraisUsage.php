<?php

namespace App\Filament\Resources\FraisUsageResource\Pages;

use App\Filament\Resources\FraisUsageResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewFraisUsage extends ViewRecord
{
    protected static string $resource = FraisUsageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\Action::make('facturer')
                ->label('Facturer')
                ->icon('heroicon-o-document-text')
                ->color('success')
                ->visible(fn () => $this->record->facturation_id === null)
                ->url(fn () => route('filament.admin.resources.facturations.create', [
                    'frais_ids' => [$this->record->id],
                ])),
            Actions\Action::make('changerStatut')
                ->label('Changer le statut')
                ->icon('heroicon-o-arrow-path')
                ->color('primary')
                ->form([
                    \Filament\Forms\Components\Select::make('statut')
                        ->label('Nouveau statut')
                        ->options([
                            'en_attente' => 'En attente',
                            'facture' => 'Facturé',
                            'paye' => 'Payé',
                            'annule' => 'Annulé',
                        ])
                        ->required(),
                ])
                ->action(function (array $data): void {
                    $this->record->update(['statut' => $data['statut']]);
                    $this->notify('success', 'Le statut a été mis à jour');
                }),
        ];
    }
}
