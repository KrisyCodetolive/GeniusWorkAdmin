<?php

namespace App\Filament\Resources\DemandeDepenseResource\Pages;

use App\Filament\Resources\DemandeDepenseResource;
use App\Models\DemandeDepense;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListDemandeDepenses extends ListRecords
{
    protected static string $resource = DemandeDepenseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Nouvelle demande'),
        ];
    }

    public function getTabs(): array
    {
        $user = auth()->user();

        $tabs = [
            'toutes' => Tab::make('Toutes'),
            'a_valider' => Tab::make('À valider par moi')
                ->icon('heroicon-o-pencil-square')
                ->badge(fn () => DemandeDepenseResource::aValiderPar(DemandeDepenseResource::getEloquentQuery(), $user)->count() ?: null)
                ->badgeColor('warning')
                ->modifyQueryUsing(fn (Builder $query) => DemandeDepenseResource::aValiderPar($query, $user)),
        ];

        if ($user->isComptable() || $user->isSuperAdmin()) {
            $tabs['a_payer'] = Tab::make('À payer')
                ->icon('heroicon-o-banknotes')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('statut', DemandeDepense::STATUT_APPROUVEE));
        }

        $tabs['mes_demandes'] = Tab::make('Mes demandes')
            ->icon('heroicon-o-user')
            ->modifyQueryUsing(fn (Builder $query) => $query->where('cree_par_user_id', $user->id));

        return $tabs;
    }
}
