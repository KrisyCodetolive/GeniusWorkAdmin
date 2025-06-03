<?php

namespace App\Filament\Resources\EmployeurResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Filters\SelectFilter;
use Filament\Forms\Components\DateTimePicker;

class PresencesRelationManager extends RelationManager
{
    protected static string $relationship = 'presences';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\DateTimePicker::make('date_heure_entree')
                    ->label('Date et heure de début')
                    ->required(),
                Forms\Components\DateTimePicker::make('date_heure_sortie')
                    ->label('Date et heure de fin')
                    ->required(),
                Forms\Components\Select::make('statut')
                    ->label('Statut')
                    ->options([
                        'present' => 'Présent',
                        'absent' => 'Absent',
                        'rejetee' => 'Rejetée',
                        'sortie' => 'Sortie',
                        'conge' => 'Congé',
                    ])
                    ->required(),
                Forms\Components\Select::make('site_id')
                    ->label('Site')
                    ->relationship('site', 'nom')
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\Textarea::make('notes')
                    ->label('Notes')
                    ->maxLength(1000),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                Tables\Columns\TextColumn::make('date_heure_entree')
                    ->label('Début')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('date_heure_sortie')
                    ->label('Fin')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('duree')
                    ->label('Durée')
                    ->getStateUsing(function ($record) {
                        $debut = $record->date_heure_entree;
                        $fin = $record->date_heure_sortie;
                        if (!$debut || !$fin) return '-';
                        
                        $interval = $debut->diff($fin);
                        return $interval->format('%H:%I');
                    }),
                Tables\Columns\TextColumn::make('statut')
                    ->label('Statut')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'present' => 'success',
                        'absent' => 'danger',
                        'sortie' => 'warning',
                        'rejetee' => 'danger',
                        'conge' => 'info',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('site.nom')
                    ->label('Site')
                    ->sortable(),
                Tables\Columns\TextColumn::make('validateur.name')
                    ->label('Validé par')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('statut')
                    ->label('Statut')
                    ->options([
                        'present' => 'Présent',
                        'absent' => 'Absent',
                        'sortie' => 'Sortie',
                        'rejetee' => 'Rejetée',
                        'conge' => 'Congé',
                    ]),
                SelectFilter::make('site_id')
                    ->label('Site')
                    ->relationship('site', 'nom'),
                Tables\Filters\Filter::make('date_heure_entree')
                    ->form([
                        Forms\Components\DatePicker::make('date_heure_entree_depuis')
                            ->label('Depuis'),
                        Forms\Components\DatePicker::make('date_heure_entree_jusqua')
                            ->label('Jusqu\'à'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['date_heure_entree_depuis'],
                                fn (Builder $query, $date): Builder => $query->whereDate('date_heure_entree', '>=', $date),
                            )
                            ->when(
                                $data['date_heure_entree_jusqua'],
                                fn (Builder $query, $date): Builder => $query->whereDate('date_heure_entree', '<=', $date),
                            );
                    }),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('date_heure_entree', 'desc');
    }
}
