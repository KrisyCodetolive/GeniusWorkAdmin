<?php

namespace App\Filament\Widgets;

use App\Models\Conge;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class CongesStatusWidget extends BaseWidget
{
    protected static ?string $heading = 'Dernières demandes de congés';
    
    protected int | string | array $columnSpan = 'full';
    
    protected function getTableQuery(): Builder
    {
        return Conge::query()
            ->with(['employeur', 'typeConge', 'validateur'])
            ->latest()
            ->limit(5);
    }
    
    protected function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('employeur.name')
                ->label('Employé')
                ->searchable(),
                
            Tables\Columns\TextColumn::make('typeConge.nom')
                ->label('Type de congé')
                ->searchable(),
                
            Tables\Columns\TextColumn::make('date_debut')
                ->label('Date de début')
                ->date('d/m/Y')
                ->sortable(),
                
            Tables\Columns\TextColumn::make('date_fin')
                ->label('Date de fin')
                ->date('d/m/Y')
                ->sortable(),
                
            Tables\Columns\TextColumn::make('duree_jours')
                ->label('Durée (jours)')
                ->numeric()
                ->sortable(),
                
            Tables\Columns\TextColumn::make('statut')
                ->label('Statut')
                ->badge()
                ->formatStateUsing(function ($state) {
                    $labels = [
                        'en_attente' => 'En attente',
                        'approuve' => 'Approuvé',
                        'rejete' => 'Rejeté',
                        'annule' => 'Annulé',
                    ];
                    
                    return $labels[$state] ?? $state;
                })
                ->colors([
                    'warning' => 'en_attente',
                    'success' => 'approuve',
                    'danger' => 'rejete',
                    'gray' => 'annule',
                ])
                ->searchable(),
                
            Tables\Columns\TextColumn::make('validateur.name')
                ->label('Validé par')
                ->placeholder('-')
                ->searchable(),
        ];
    }
}
