<?php

namespace App\Filament\Resources\VisiteurResource\Pages;

use App\Filament\Resources\VisiteurResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListVisiteurs extends ListRecords
{
    protected static string $resource = VisiteurResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label(function () {
                    $entrepriseId = auth()->user()->entreprise_id;
                    $visiteursUtilises = \App\Models\Visiteur::where('entreprise_id', $entrepriseId)->count();
                    $visiteursMax = 1000; // Limite maximale de visiteurs
                    $visiteursRestants = max(0, $visiteursMax - $visiteursUtilises);
                    
                    return $visiteursRestants . ' Visiteurs Restants';
                })
                ->icon('heroicon-o-users')
                ->color(function () {
                    $entrepriseId = auth()->user()->entreprise_id;
                    $visiteursUtilises = \App\Models\Visiteur::where('entreprise_id', $entrepriseId)->count();
                    $visiteursMax = 1000; // Limite maximale de visiteurs
                    $visiteursRestants = max(0, $visiteursMax - $visiteursUtilises);
                    
                    // Rouge si moins de 5% restants, orange si moins de 15%, vert sinon
                    if ($visiteursRestants <= 50) {
                        return 'danger';
                    } elseif ($visiteursRestants <= 150) {
                        return 'warning';
                    } else {
                        return 'success';
                    }
                })
                ->tooltip(function () {
                    $entrepriseId = auth()->user()->entreprise_id;
                    $visiteursUtilises = \App\Models\Visiteur::where('entreprise_id', $entrepriseId)->count();
                    $visiteursMax = 1000; // Limite maximale de visiteurs
                    $visiteursRestants = max(0, $visiteursMax - $visiteursUtilises);
                    
                    return "Vous pouvez encore créer $visiteursRestants visiteurs sur un total de $visiteursMax";
                })
                ->visible(fn (): bool => auth()->user()->isAdmin() || auth()->user()->isSuperAdmin() || auth()->user()->isSupport()),
            
            Actions\CreateAction::make()
                ->label('Nouveau visiteur')
                ->icon('heroicon-o-user-plus'),
        ];
    }
}
