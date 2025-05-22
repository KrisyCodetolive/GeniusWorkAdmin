<?php

namespace App\Filament\Resources\EntrepriseResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Models\Visite;
use App\Models\Visiteur;
use App\Models\Site;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Model;

class VisitesRelationManager extends RelationManager
{
    protected static string $relationship = 'visites';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('visiteur_id')
                    ->label('Visiteur')
                    ->options(function (Model $record = null): array {
                        $entrepriseId = $this->getOwnerRecord()->id;
                        return Visiteur::where('entreprise_id', $entrepriseId)
                            ->get()
                            ->mapWithKeys(function ($visiteur) {
                                return [$visiteur->id => $visiteur->nom_complet . ' (' . $visiteur->telephone . ')'];
                            })
                            ->toArray();
                    })
                    ->required()
                    ->searchable()
                    ->preload(),
                
                Select::make('site_id')
                    ->label('Site')
                    ->options(function (Model $record = null): array {
                        $entrepriseId = $this->getOwnerRecord()->id;
                        return Site::where('entreprise_id', $entrepriseId)
                            ->pluck('nom', 'id')
                            ->toArray();
                    })
                    ->required()
                    ->searchable(),
                
                TextInput::make('motif_visite')
                    ->label('Motif de la visite')
                    ->required(),
                
                TextInput::make('personne_a_rencontrer')
                    ->label('Personne à rencontrer'),
                
                DateTimePicker::make('date_arrivee')
                    ->label('Date d\'arrivée')
                    ->required()
                    ->default(now()),
                
                DateTimePicker::make('date_depart')
                    ->label('Date de départ')
                    ->nullable(),
                
                TextInput::make('departement_a_visiter')
                    ->label('Département à visiter'),
                
                TextInput::make('badge_visiteur')
                    ->label('Badge visiteur')
                    ->placeholder('Numéro de badge attribué'),
                
                Select::make('statut')
                    ->label('Statut')
                    ->options([
                        'en_cours' => 'En cours',
                        'terminee' => 'Terminée',
                        'annulee' => 'Annulée',
                    ])
                    ->default('en_cours')
                    ->required(),
                
                Textarea::make('commentaires')
                    ->label('Commentaires')
                    ->rows(3),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                Tables\Columns\TextColumn::make('visiteur.nom_complet')
                    ->label('Visiteur')
                    ->searchable(['visiteur.nom', 'visiteur.prenom']),
                
                Tables\Columns\TextColumn::make('site.nom')
                    ->label('Site')
                    ->searchable(),
                
                Tables\Columns\TextColumn::make('motif_visite')
                    ->label('Motif')
                    ->searchable()
                    ->limit(30),
                
                Tables\Columns\TextColumn::make('date_arrivee')
                    ->label('Arrivée')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('date_depart')
                    ->label('Départ')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->placeholder('En cours'),
                
                Tables\Columns\TextColumn::make('personne_a_rencontrer')
                    ->label('Personne à rencontrer')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                Tables\Columns\TextColumn::make('badge_visiteur')
                    ->label('Badge')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                Tables\Columns\TextColumn::make('statut')
                    ->label('Statut')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'en_cours' => 'primary',
                        'terminee' => 'success',
                        'annulee' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'en_cours' => 'En cours',
                        'terminee' => 'Terminée',
                        'annulee' => 'Annulée',
                        default => $state,
                    }),
            ])
            ->filters([
                SelectFilter::make('statut')
                    ->label('Statut')
                    ->options([
                        'en_cours' => 'En cours',
                        'terminee' => 'Terminée',
                        'annulee' => 'Annulée',
                    ]),
                
                Filter::make('date_arrivee')
                    ->form([
                        DateTimePicker::make('date_arrivee_depuis')
                            ->label('Depuis'),
                        DateTimePicker::make('date_arrivee_jusqu_a')
                            ->label('Jusqu\'à'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['date_arrivee_depuis'],
                                fn (Builder $query, $date): Builder => $query->whereDate('date_arrivee', '>=', $date),
                            )
                            ->when(
                                $data['date_arrivee_jusqu_a'],
                                fn (Builder $query, $date): Builder => $query->whereDate('date_arrivee', '<=', $date),
                            );
                    }),
                
                SelectFilter::make('site_id')
                    ->label('Site')
                    ->options(function (): array {
                        $entrepriseId = $this->getOwnerRecord()->id;
                        return Site::where('entreprise_id', $entrepriseId)
                            ->pluck('nom', 'id')
                            ->toArray();
                    }),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\Action::make('terminer')
                    ->label('Terminer')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->action(function (Visite $record) {
                        $record->update([
                            'statut' => 'terminee',
                            'date_depart' => now(),
                        ]);
                    })
                    ->requiresConfirmation()
                    ->visible(fn (Visite $record) => $record->statut === 'en_cours'),
                
                Tables\Actions\Action::make('annuler')
                    ->label('Annuler')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->action(function (Visite $record) {
                        $record->update([
                            'statut' => 'annulee',
                        ]);
                    })
                    ->requiresConfirmation()
                    ->visible(fn (Visite $record) => $record->statut === 'en_cours'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('terminer_visites')
                        ->label('Terminer les visites')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(function (Collection $records) {
                            $records->each(function (Visite $record) {
                                if ($record->statut === 'en_cours') {
                                    $record->update([
                                        'statut' => 'terminee',
                                        'date_depart' => now(),
                                    ]);
                                }
                            });
                        })
                        ->requiresConfirmation(),
                ]),
            ]);
    }
}
