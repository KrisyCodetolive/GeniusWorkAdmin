<?php

namespace App\Filament\Resources\JourResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Filters\SelectFilter;
use Carbon\Carbon;

class PresencesRelationManager extends RelationManager
{
    protected static string $relationship = 'presences';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\DateTimePicker::make('heure_debut')
                    ->label('Heure de début')
                    ->required(),
                Forms\Components\DateTimePicker::make('heure_fin')
                    ->label('Heure de fin')
                    ->after('heure_debut'),
                Forms\Components\Select::make('statut')
                    ->label('Statut')
                    ->options([
                        'present' => 'Présent',
                        'absent' => 'Absent',
                        'retard' => 'En retard',
                        'depart_anticipe' => 'Départ anticipé',
                    ])
                    ->default('present')
                    ->required(),
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
                Tables\Columns\TextColumn::make('heure_debut')
                    ->label('Début')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('heure_fin')
                    ->label('Fin')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('duree')
                    ->label('Durée')
                    ->getStateUsing(function ($record) {
                        if (!$record->heure_fin) return '-';
                        $minutes = Carbon::parse($record->heure_debut)->diffInMinutes(Carbon::parse($record->heure_fin));
                        $heures = floor($minutes / 60);
                        $minutesRestantes = $minutes % 60;
                        return sprintf('%02d:%02d', $heures, $minutesRestantes);
                    }),
                Tables\Columns\TextColumn::make('statut')
                    ->label('Statut')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'present' => 'success',
                        'absent' => 'danger',
                        'retard' => 'warning',
                        'depart_anticipe' => 'warning',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('commentaire')
                    ->label('Commentaire')
                    ->limit(30)
                    ->toggleable(isToggledHiddenByDefault: true),
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
                        'retard' => 'En retard',
                        'depart_anticipe' => 'Départ anticipé',
                    ]),
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
            ->defaultSort('heure_debut', 'desc');
    }
}
