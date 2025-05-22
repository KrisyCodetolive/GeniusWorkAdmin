<?php

namespace App\Filament\Resources\SoldeCongeResource\Pages;

use App\Filament\Resources\SoldeCongeResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewSoldeConge extends ViewRecord
{
    protected static string $resource = SoldeCongeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\Action::make('ajouter')
                ->label('Ajouter des jours')
                ->icon('heroicon-o-plus')
                ->color('success')
                ->form([
                    \Filament\Forms\Components\TextInput::make('jours')
                        ->label('Nombre de jours')
                        ->required()
                        ->numeric()
                        ->step(0.5)
                        ->minValue(0.5),
                    \Filament\Forms\Components\Textarea::make('motif')
                        ->label('Motif')
                        ->required()
                        ->maxLength(255),
                ])
                ->action(function (array $data) {
                    $this->record->ajouterSolde($data['jours'], $data['motif']);
                    $this->redirect(SoldeCongeResource::getUrl('view', ['record' => $this->record]));
                }),
            Actions\Action::make('deduire')
                ->label('Déduire des jours')
                ->icon('heroicon-o-minus')
                ->color('danger')
                ->form([
                    \Filament\Forms\Components\TextInput::make('jours')
                        ->label('Nombre de jours')
                        ->required()
                        ->numeric()
                        ->step(0.5)
                        ->minValue(0.5),
                    \Filament\Forms\Components\Textarea::make('motif')
                        ->label('Motif')
                        ->required()
                        ->maxLength(255),
                ])
                ->action(function (array $data) {
                    $this->record->deduireSolde($data['jours'], $data['motif']);
                    $this->redirect(SoldeCongeResource::getUrl('view', ['record' => $this->record]));
                }),
        ];
    }
}
