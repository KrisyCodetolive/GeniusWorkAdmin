<?php

namespace App\Filament\Resources\CongeResource\Pages;

use App\Filament\Resources\CongeResource;
use App\Filament\Resources\CongeResource\Widgets\CongeStatsWidget;
use App\Filament\Resources\TypeCongeResource;
use App\Filament\Resources\SoldeCongeResource;
use App\Filament\Pages\CongesStatistiquesPage;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Actions\GenerateDemandesCongesAction;

class ListConges extends ListRecords
{
    protected static string $resource = CongeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Page vue Stats pour les congés
            Actions\Action::make('stats')
                ->label('Voir les stats')
                ->icon('heroicon-o-chart-bar')
                ->color('success')
                ->url(fn (): string => CongesStatistiquesPage::getUrl()),
            GenerateDemandesCongesAction::make()
                ->label('Générer des demandes')
                ->icon('heroicon-o-calendar')
                ->color('warning')
                ->visible(fn (): bool => auth()->user()->isAdmin() || auth()->user()->isSuperAdmin() || auth()->user()->isSupport()),
            Actions\Action::make('typeConges')
                ->label('Types de congés')
                ->icon('heroicon-o-tag')
                ->color('success')
                ->url(fn (): string => TypeCongeResource::getUrl())
                ->visible(fn (): bool => auth()->user()->isAdmin() || auth()->user()->isSuperAdmin() || auth()->user()->isSupport()),
            Actions\Action::make('soldeConges')
                ->label('Soldes de congés')
                ->icon('heroicon-o-calculator')
                ->color('warning')
                ->url(fn (): string => SoldeCongeResource::getUrl())
                ->visible(fn (): bool => auth()->user()->isAdmin() || auth()->user()->isSuperAdmin() || auth()->user()->isSupport()),
            Actions\CreateAction::make()
                ->label('Nouveau congé')
                ->icon('heroicon-o-plus')
                ->visible(fn (): bool => auth()->user()->isAdmin() || auth()->user()->isSuperAdmin() || auth()->user()->isSupport()),
        ];
    }
    
    protected function getHeaderWidgets(): array
    {
        return [
            CongeStatsWidget::class,
        ];
    }
}
