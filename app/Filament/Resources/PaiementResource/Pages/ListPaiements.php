<?php

namespace App\Filament\Resources\PaiementResource\Pages;

use App\Filament\Resources\PaiementResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListPaiements extends ListRecords
{
    protected static string $resource = PaiementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
    
    public function getTabs(): array
    {
        $tabs = [
            'tous' => Tab::make('Tous les paiements')
                ->badge(function () {
                    $query = \App\Models\Paiement::query();
                    
                    // Si l'utilisateur n'est pas un super admin, filtrer par entreprise
                    if (!auth()->user()->isSuperAdmin()) {
                        $query->where('entreprise_id', auth()->user()->entreprise_id);
                    }
                    
                    return $query->count();
                }),
            'en_attente' => Tab::make('En attente')
                ->badge(function () {
                    $query = \App\Models\Paiement::query()
                        ->where('statut', 'en_attente');
                    
                    // Si l'utilisateur n'est pas un super admin, filtrer par entreprise
                    if (!auth()->user()->isSuperAdmin()) {
                        $query->where('entreprise_id', auth()->user()->entreprise_id);
                    }
                    
                    return $query->count();
                })
                ->badgeColor('warning')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('statut', 'en_attente')),
            'completes' => Tab::make('Complétés')
                ->badge(function () {
                    $query = \App\Models\Paiement::query()
                        ->where('statut', 'complete');
                    
                    // Si l'utilisateur n'est pas un super admin, filtrer par entreprise
                    if (!auth()->user()->isSuperAdmin()) {
                        $query->where('entreprise_id', auth()->user()->entreprise_id);
                    }
                    
                    return $query->count();
                })
                ->badgeColor('success')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('statut', 'complete')),
            'echoues' => Tab::make('Échoués')
                ->badge(function () {
                    $query = \App\Models\Paiement::query()
                        ->whereIn('statut', ['echoue', 'rejete']);
                    
                    // Si l'utilisateur n'est pas un super admin, filtrer par entreprise
                    if (!auth()->user()->isSuperAdmin()) {
                        $query->where('entreprise_id', auth()->user()->entreprise_id);
                    }
                    
                    return $query->count();
                })
                ->badgeColor('danger')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereIn('statut', ['echoue', 'rejete'])),
        ];
        
        // Ajouter l'onglet "À valider" uniquement pour les super admins
        if (auth()->user()->isSuperAdmin()) {
            $tabs['a_valider'] = Tab::make('À valider')
                ->badge(function () {
                    return \App\Models\Paiement::query()
                        ->where('passerelle', 'manuel')
                        ->where('statut', 'en_attente')
                        ->count();
                })
                ->badgeColor('primary')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('passerelle', 'manuel')->where('statut', 'en_attente'));
        }
        
        return $tabs;
    }
}
