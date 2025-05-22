<?php

namespace App\Filament\Resources\RapportResource\Pages;

use App\Filament\Resources\RapportResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListRapports extends ListRecords
{
    protected static string $resource = RapportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Actions\Action::make('rapportFinancierGlobal')
                ->label('Rapport financier global')
                ->icon('heroicon-o-currency-dollar')
                ->color('success')
                ->action(function () {
                    $service = app(\App\Services\RapportFinancierService::class);
                    
                    // Générer un rapport financier global
                    $rapport = $service->genererRapportGlobal([
                        'date_debut' => now()->startOfYear(),
                        'date_fin' => now(),
                    ]);
                    
                    // Créer un enregistrement de rapport
                    $nouveauRapport = \App\Models\Rapport::create([
                        'titre' => 'Rapport financier global - ' . now()->format('d/m/Y'),
                        'description' => 'Rapport financier global généré automatiquement',
                        'type' => 'financier',
                        'format' => 'pdf',
                        'parametres' => $rapport,
                        'date_debut' => now()->startOfYear(),
                        'date_fin' => now(),
                        'cree_par' => auth()->id(),
                    ]);
                    
                    return redirect()->route('filament.admin.resources.rapports.view', ['record' => $nouveauRapport->id]);
                }),
            Actions\Action::make('rapportPrevisionsFinancieres')
                ->label('Prévisions financières')
                ->icon('heroicon-o-chart-bar')
                ->color('primary')
                ->action(function () {
                    $service = app(\App\Services\RapportFinancierService::class);
                    
                    // Générer un rapport de prévisions financières
                    $rapport = $service->genererPrevisionsFinancieres([
                        'nombre_mois' => 6,
                    ]);
                    
                    // Créer un enregistrement de rapport
                    $nouveauRapport = \App\Models\Rapport::create([
                        'titre' => 'Prévisions financières - ' . now()->format('d/m/Y'),
                        'description' => 'Prévisions financières pour les 6 prochains mois',
                        'type' => 'financier',
                        'format' => 'pdf',
                        'parametres' => $rapport,
                        'date_debut' => now(),
                        'date_fin' => now()->addMonths(6),
                        'cree_par' => auth()->id(),
                    ]);
                    
                    return redirect()->route('filament.admin.resources.rapports.view', ['record' => $nouveauRapport->id]);
                }),
        ];
    }
}
