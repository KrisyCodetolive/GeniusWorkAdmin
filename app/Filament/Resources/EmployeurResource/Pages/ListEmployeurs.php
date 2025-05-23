<?php

namespace App\Filament\Resources\EmployeurResource\Pages;

use App\Filament\Resources\EmployeurResource;
use App\Filament\Widgets\EmployeurStatsWidget;
use App\Filament\Widgets\EmployeurDepartementWidget;
use App\Filament\Widgets\EmployeurTendanceWidget;
use App\Filament\Actions\GenerateEmployeursExemplesAction;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListEmployeurs extends ListRecords
{
    protected static string $resource = EmployeurResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            // Page vue Stats pour les employeurs
            Actions\Action::make('stats')
                ->label('Voir les stats')
                ->icon('heroicon-o-chart-bar')
                ->color('success')
                ->url(fn () => route('filament.admin.pages.employeurs-stats')),
            // Action pour générer des employés exemples
            GenerateEmployeursExemplesAction::make()
                ->visible(function () {
                    $user = auth()->user();
                    
                    // Vérifier si l'utilisateur a les droits nécessaires
                    if (!($user->isAdmin() || $user->isSuperAdmin() || $user->isSupport())) {
                        return false;
                    }
                    
                    $entreprise = $user->entreprise;
                    if (!$entreprise) {
                        return false;
                    }
                    
                    // Vérifier si l'entreprise a déjà des employeurs
                    $existingEmployeurs = \App\Models\Employeur::where('entreprise_id', $entreprise->id)->count();
                    
                    // Ne montrer l'action que si l'entreprise n'a pas encore d'employeurs
                    return $existingEmployeurs === 0;
                }),
        ];
    }
    
    protected function getHeaderWidgets(): array
    {
        return [
            EmployeurStatsWidget::class,

        ];
    }
}
