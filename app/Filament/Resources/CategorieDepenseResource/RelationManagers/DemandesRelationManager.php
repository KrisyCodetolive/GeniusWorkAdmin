<?php

namespace App\Filament\Resources\CategorieDepenseResource\RelationManagers;

use App\Filament\Resources\DemandeDepenseResource;
use App\Models\DemandeDepense;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

/**
 * Demandes imputées sur une catégorie, en lecture seule : elles se traitent dans « Demandes de dépense ».
 */
class DemandesRelationManager extends RelationManager
{
    protected static string $relationship = 'demandes';

    protected static ?string $title = 'Demandes de dépense';

    protected static ?string $icon = 'heroicon-o-banknotes';

    protected static ?string $modelLabel = 'demande de dépense';

    protected static ?string $pluralModelLabel = 'demandes de dépense';

    public static function getBadge(Model $ownerRecord, string $pageClass): ?string
    {
        return (string) $ownerRecord->demandes()->count() ?: null;
    }

    public function isReadOnly(): bool
    {
        return true;
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('reference')
            ->columns([
                Tables\Columns\TextColumn::make('reference')
                    ->label('Référence')
                    ->searchable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('objet')
                    ->label('Objet')
                    ->searchable()
                    ->limit(40),
                Tables\Columns\TextColumn::make('montant')
                    ->label('Montant')
                    ->formatStateUsing(fn ($state, DemandeDepense $record) => number_format((float) $state, 0, ',', ' ').' '.$record->devise)
                    ->alignEnd()
                    ->sortable()
                    ->summarize(Tables\Columns\Summarizers\Sum::make()
                        ->label('Total')
                        ->numeric(decimalPlaces: 0, thousandsSeparator: ' ')),
                Tables\Columns\TextColumn::make('statut')
                    ->label('Statut')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => DemandeDepense::STATUTS[$state] ?? $state)
                    ->color(fn (string $state) => DemandeDepense::COULEURS_STATUT[$state] ?? 'gray'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Créée le')
                    ->date('d/m/Y')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('statut')
                    ->label('Statut')
                    ->options(DemandeDepense::STATUTS),
            ])
            ->actions([
                Tables\Actions\Action::make('voir')
                    ->label('Voir')
                    ->icon('heroicon-o-eye')
                    ->url(fn (DemandeDepense $record) => DemandeDepenseResource::getUrl('view', ['record' => $record])),
            ])
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('Aucune demande sur cette catégorie');
    }
}
