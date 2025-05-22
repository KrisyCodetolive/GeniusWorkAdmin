<?php

namespace App\Filament\Resources\SoldeCongeResource\Pages;

use App\Filament\Actions\GenerateSoldesCongesAction;
use App\Filament\Actions\GenerateDemandesCongesAction;
use App\Filament\Resources\SoldeCongeResource;
use App\Filament\Resources\CongeResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSoldeConges extends ListRecords
{
    protected static string $resource = SoldeCongeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            GenerateSoldesCongesAction::make()
                ->label('Générer des soldes')
                ->icon('heroicon-o-calculator')
                ->color('success')
                ->visible(fn (): bool => auth()->user()->isAdmin() || auth()->user()->isSuperAdmin() || auth()->user()->isSupport()),
            
            GenerateDemandesCongesAction::make()
                ->label('Générer des demandes')
                ->icon('heroicon-o-calendar')
                ->color('warning')
                ->visible(fn (): bool => auth()->user()->isAdmin() || auth()->user()->isSuperAdmin() || auth()->user()->isSupport()),
            
            Actions\Action::make('voirDemandes')
                ->label('Voir les demandes')
                ->icon('heroicon-o-eye')
                ->url(fn (): string => CongeResource::getUrl())
                ->color('info'),
            
            Actions\CreateAction::make()
                ->label('Nouveau solde')
                ->icon('heroicon-o-plus'),
        ];
    }
}
