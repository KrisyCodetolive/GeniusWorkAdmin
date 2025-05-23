<?php

namespace App\Filament\Resources\Paie\BulletinPaieResource\Pages;

use App\Filament\Actions\ExporterBulletinsPaieAction;
use App\Filament\Actions\GenerateBulletinsPaieTestAction;
use App\Filament\Actions\GenererRapportPaieAction;
use App\Filament\Resources\Paie\BulletinPaieResource;
use App\Filament\Resources\Paie\ConfigurationPaieResource;
use App\Models\Paie\BulletinPaie;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;

class ListBulletinPaies extends ListRecords
{
    protected static string $resource = BulletinPaieResource::class;
    
    public function getTabs(): array
    {
        $entrepriseId = Auth::user()->entreprise_id;
        $now = now();
        
        return [
            // Onglets par statut
            'tous' => Tab::make('Tous les bulletins')
                ->icon('heroicon-o-document-text')
                ->badge(BulletinPaie::where('entreprise_id', $entrepriseId)->count()),
                
            'brouillons' => Tab::make('Brouillons')
                ->icon('heroicon-o-clock')
                ->badge(BulletinPaie::where('entreprise_id', $entrepriseId)
                    ->where('statut', 'brouillon')
                    ->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->where('statut', 'brouillon'))
                ->badgeColor('warning'),
                
            'valides' => Tab::make('Validés')
                ->icon('heroicon-o-check')
                ->badge(BulletinPaie::where('entreprise_id', $entrepriseId)
                    ->where('statut', 'validé')
                    ->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->where('statut', 'validé'))
                ->badgeColor('success'),
                
            'annules' => Tab::make('Annulés')
                ->icon('heroicon-o-x-mark')
                ->badge(BulletinPaie::where('entreprise_id', $entrepriseId)
                    ->where('statut', 'annulé')
                    ->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->where('statut', 'annulé'))
                ->badgeColor('danger'),
                
            // Onglets par période
            'ce_mois' => Tab::make('Ce mois')
                ->icon('heroicon-o-calendar')
                ->badge(BulletinPaie::where('entreprise_id', $entrepriseId)
                    ->whereMonth('periode_fin', $now->month)
                    ->whereYear('periode_fin', $now->year)
                    ->count())
                ->modifyQueryUsing(fn (Builder $query) => $query
                    ->whereMonth('periode_fin', $now->month)
                    ->whereYear('periode_fin', $now->year))
                ->badgeColor('primary'),
                
            'mois_precedent' => Tab::make('Mois précédent')
                ->icon('heroicon-o-calendar')
                ->badge(BulletinPaie::where('entreprise_id', $entrepriseId)
                    ->whereMonth('periode_fin', $now->copy()->subMonth()->month)
                    ->whereYear('periode_fin', $now->copy()->subMonth()->year)
                    ->count())
                ->modifyQueryUsing(fn (Builder $query) => $query
                    ->whereMonth('periode_fin', $now->copy()->subMonth()->month)
                    ->whereYear('periode_fin', $now->copy()->subMonth()->year)),
                
            'trimestre' => Tab::make('Trimestre en cours')
                ->icon('heroicon-o-calendar')
                ->badge(BulletinPaie::where('entreprise_id', $entrepriseId)
                    ->whereBetween('periode_fin', [
                        $now->copy()->startOfQuarter(),
                        $now->copy()->endOfQuarter()
                    ])
                    ->count())
                ->modifyQueryUsing(fn (Builder $query) => $query
                    ->whereBetween('periode_fin', [
                        $now->copy()->startOfQuarter(),
                        $now->copy()->endOfQuarter()
                    ])),
                
            'annee' => Tab::make('Année ' . $now->year)
                ->icon('heroicon-o-calendar')
                ->badge(BulletinPaie::where('entreprise_id', $entrepriseId)
                    ->whereYear('periode_fin', $now->year)
                    ->count())
                ->modifyQueryUsing(fn (Builder $query) => $query
                    ->whereYear('periode_fin', $now->year)),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Nouveau bulletin')
                ->icon('heroicon-o-plus')
                ->color('success')
                ->tooltip('Créer un nouveau bulletin de paie')
                ->visible(fn (): bool => auth()->user()->isAdmin() || auth()->user()->isSuperAdmin() || auth()->user()->isSupport()),
    
            Actions\Action::make('configurations')
                ->label('Configurations')
                ->icon('heroicon-o-cog')
                ->color('danger')
                ->tooltip('Gérer les configurations de paie')
                ->url(fn (): string => ConfigurationPaieResource::getUrl())
                ->visible(fn (): bool => auth()->user()->isAdmin() || auth()->user()->isSuperAdmin() || auth()->user()->isSupport()),
                
            ExporterBulletinsPaieAction::make()
                ->visible(fn (): bool => auth()->user()->isAdmin() || auth()->user()->isSuperAdmin()),
                
            GenererRapportPaieAction::make()
                ->visible(fn (): bool => auth()->user()->isAdmin() || auth()->user()->isSuperAdmin()),
                
            GenerateBulletinsPaieTestAction::make()
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
                    
                    // Vérifier si l'entreprise a déjà des bulletins de paie
                    $existingBulletins = \App\Models\Paie\BulletinPaie::where('entreprise_id', $entreprise->id)->count();
                    
                    // Ne montrer l'action que si l'entreprise n'a pas encore de bulletins de paie
                    return $existingBulletins === 0;
                }),
        ];
    }
    
    protected function getHeaderWidgets(): array
    {
        return [
            // Vous pourrez ajouter des widgets ici plus tard
            // Exemple : BulletinPaieStats::class,
        ];
    }
    
    protected function getActions(): array
    {
        return [
            Actions\Action::make('refresh')
                ->label('Actualiser')
                ->icon('heroicon-o-arrow-path')
                ->action(fn () => $this->refreshData()),
        ];
    }
}
