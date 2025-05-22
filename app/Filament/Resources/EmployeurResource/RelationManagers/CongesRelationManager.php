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
use Filament\Forms\Components\DatePicker;

class CongesRelationManager extends RelationManager
{
    protected static string $relationship = 'conges';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\DatePicker::make('date_debut')
                    ->label('Date de début')
                    ->required(),
                Forms\Components\DatePicker::make('date_fin')
                    ->label('Date de fin')
                    ->required(),
                Forms\Components\Select::make('type')
                    ->label('Type de congé')
                    ->options([
                        'annuel' => 'Congé annuel',
                        'maladie' => 'Congé maladie',
                        'maternite' => 'Congé maternité',
                        'paternite' => 'Congé paternité',
                        'sans_solde' => 'Congé sans solde',
                        'special' => 'Congé spécial',
                    ])
                    ->required(),
                Forms\Components\Select::make('statut')
                    ->label('Statut')
                    ->options([
                        'en_attente' => 'En attente',
                        'approuve' => 'Approuvé',
                        'rejete' => 'Rejeté',
                        'annule' => 'Annulé',
                    ])
                    ->default('en_attente')
                    ->required(),
                Forms\Components\Textarea::make('motif')
                    ->label('Motif')
                    ->maxLength(1000),
                Forms\Components\Textarea::make('commentaire')
                    ->label('Commentaire')
                    ->maxLength(1000),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                Tables\Columns\TextColumn::make('date_debut')
                    ->label('Début')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('date_fin')
                    ->label('Fin')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('duree')
                    ->label('Durée (jours)')
                    ->getStateUsing(function ($record) {
                        $debut = $record->date_debut;
                        $fin = $record->date_fin;
                        if (!$debut || !$fin) return '-';
                        
                        return $debut->diffInDays($fin) + 1;
                    }),
                Tables\Columns\TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'annuel' => 'Congé annuel',
                        'maladie' => 'Congé maladie',
                        'maternite' => 'Congé maternité',
                        'paternite' => 'Congé paternité',
                        'sans_solde' => 'Congé sans solde',
                        'special' => 'Congé spécial',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'annuel' => 'info',
                        'maladie' => 'danger',
                        'maternite' => 'purple',
                        'paternite' => 'purple',
                        'sans_solde' => 'gray',
                        'special' => 'warning',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('statut')
                    ->label('Statut')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'approuve' => 'success',
                        'en_attente' => 'warning',
                        'rejete' => 'danger',
                        'annule' => 'gray',
                        default => 'gray',
                    }),
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
                SelectFilter::make('type')
                    ->label('Type')
                    ->options([
                        'annuel' => 'Congé annuel',
                        'maladie' => 'Congé maladie',
                        'maternite' => 'Congé maternité',
                        'paternite' => 'Congé paternité',
                        'sans_solde' => 'Congé sans solde',
                        'special' => 'Congé spécial',
                    ]),
                SelectFilter::make('statut')
                    ->label('Statut')
                    ->options([
                        'en_attente' => 'En attente',
                        'approuve' => 'Approuvé',
                        'rejete' => 'Rejeté',
                        'annule' => 'Annulé',
                    ]),
                Tables\Filters\Filter::make('date_debut')
                    ->form([
                        Forms\Components\DatePicker::make('date_debut_depuis')
                            ->label('Depuis'),
                        Forms\Components\DatePicker::make('date_debut_jusqua')
                            ->label('Jusqu\'à'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['date_debut_depuis'],
                                fn (Builder $query, $date): Builder => $query->whereDate('date_debut', '>=', $date),
                            )
                            ->when(
                                $data['date_debut_jusqua'],
                                fn (Builder $query, $date): Builder => $query->whereDate('date_debut', '<=', $date),
                            );
                    }),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\Action::make('approuver')
                    ->label('Approuver')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn ($record) => $record->statut === 'en_attente')
                    ->action(function ($record) {
                        $record->update([
                            'statut' => 'approuve',
                            'validateur_id' => auth()->id(),
                        ]);
                    }),
                Tables\Actions\Action::make('rejeter')
                    ->label('Rejeter')
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn ($record) => $record->statut === 'en_attente')
                    ->action(function ($record) {
                        $record->update([
                            'statut' => 'rejete',
                            'validateur_id' => auth()->id(),
                        ]);
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('approuverMultiple')
                        ->label('Approuver la sélection')
                        ->icon('heroicon-o-check')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function ($records) {
                            foreach ($records as $record) {
                                if ($record->statut === 'en_attente') {
                                    $record->update([
                                        'statut' => 'approuve',
                                        'validateur_id' => auth()->id(),
                                    ]);
                                }
                            }
                        }),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
