<?php

namespace App\Filament\Resources\SiteResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Models\Presence;

class PointagesRelationManager extends RelationManager
{
    protected static string $relationship = 'pointages';

    protected static ?string $recordTitleAttribute = 'id';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('employeur_id')
                    ->label('Employé')
                    ->relationship('employeur', 'nom')
                    ->searchable()
                    ->required(),
                Forms\Components\DateTimePicker::make('date_heure')
                    ->label('Date et heure')
                    ->required(),
                Forms\Components\Select::make('type')
                    ->label('Type')
                    ->options([
                        'entree' => 'Entrée',
                        'sortie' => 'Sortie',
                        'pause_debut' => 'Début de pause',
                        'pause_fin' => 'Fin de pause',
                    ])
                    ->required(),
                Forms\Components\TextInput::make('latitude')
                    ->label('Latitude')
                    ->numeric()
                    ->required(),
                Forms\Components\TextInput::make('longitude')
                    ->label('Longitude')
                    ->numeric()
                    ->required(),
                Forms\Components\Toggle::make('valide')
                    ->label('Validé')
                    ->default(true),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('employeur.nom')
                    ->label('Employé')
                    ->formatStateUsing(function ($record) {
                        if (!$record->employeur) return '-';
                        return "{$record->employeur->prenom} {$record->employeur->nom} ({$record->employeur->matricule})";
                    })
                    ->searchable(['employeur.nom', 'employeur.prenom', 'employeur.matricule'])
                    ->sortable(),
                Tables\Columns\TextColumn::make('date_heure')
                    ->label('Date et heure')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'entree' => 'success',
                        'sortie' => 'danger',
                        'pause_debut' => 'warning',
                        'pause_fin' => 'info',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'entree' => 'Entrée',
                        'sortie' => 'Sortie',
                        'pause_debut' => 'Début de pause',
                        'pause_fin' => 'Fin de pause',
                        default => $state,
                    }),
                Tables\Columns\IconColumn::make('valide')
                    ->label('Validé')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('Type')
                    ->options([
                        'entree' => 'Entrée',
                        'sortie' => 'Sortie',
                        'pause_debut' => 'Début de pause',
                        'pause_fin' => 'Fin de pause',
                    ]),
                Tables\Filters\Filter::make('date')
                    ->form([
                        Forms\Components\DatePicker::make('date_from')
                            ->label('Du'),
                        Forms\Components\DatePicker::make('date_until')
                            ->label('Au'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['date_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('date_heure', '>=', $date),
                            )
                            ->when(
                                $data['date_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('date_heure', '<=', $date),
                            );
                    }),
                Tables\Filters\TernaryFilter::make('valide')
                    ->label('Validé')
                    ->queries(
                        true: fn (Builder $query) => $query->where('valide', true),
                        false: fn (Builder $query) => $query->where('valide', false),
                    ),
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
            ]);
    }
}
