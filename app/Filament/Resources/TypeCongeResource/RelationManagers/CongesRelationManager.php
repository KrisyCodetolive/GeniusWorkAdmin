<?php

namespace App\Filament\Resources\TypeCongeResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Support\Enums\FontWeight;

class CongesRelationManager extends RelationManager
{
    protected static string $relationship = 'conges';

    protected static ?string $recordTitleAttribute = 'motif';

    protected static ?string $title = 'Demandes de congés';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informations de la demande')
                    ->schema([
                        Forms\Components\Select::make('employeur_id')
                            ->relationship('employeur', 'nom')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\DatePicker::make('date_debut')
                            ->label('Date de début')
                            ->required(),
                        Forms\Components\DatePicker::make('date_fin')
                            ->label('Date de fin')
                            ->required()
                            ->after('date_debut'),
                        Forms\Components\TextInput::make('duree_jours')
                            ->label('Durée (jours)')
                            ->numeric()
                            ->step(0.5)
                            ->required(),
                        Forms\Components\Textarea::make('motif')
                            ->maxLength(255),
                        Forms\Components\FileUpload::make('justificatif')
                            ->directory('justificatifs/conges')
                            ->visibility('public')
                            ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png']),
                    ])
                    ->columns(2),
                
                Forms\Components\Section::make('Statut et validation')
                    ->schema([
                        Forms\Components\Select::make('statut')
                            ->options([
                                'en_attente' => 'En attente',
                                'approuve' => 'Approuvé',
                                'rejete' => 'Rejeté',
                                'annule' => 'Annulé',
                            ])
                            ->required(),
                        Forms\Components\Select::make('validateur_id')
                            ->relationship('validateur', 'name')
                            ->searchable()
                            ->preload(),
                        Forms\Components\DateTimePicker::make('date_validation')
                            ->label('Date de validation'),
                        Forms\Components\Textarea::make('commentaire_validation')
                            ->label('Commentaire de validation')
                            ->maxLength(255),
                        Forms\Components\Toggle::make('est_paye')
                            ->label('Congé payé'),
                    ])
                    ->columns(2),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('motif')
            ->columns([
                Tables\Columns\TextColumn::make('employeur.nom')
                    ->label('Employé')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('date_debut')
                    ->label('Début')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('date_fin')
                    ->label('Fin')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('duree_jours')
                    ->label('Durée')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('motif')
                    ->limit(30)
                    ->searchable(),
                Tables\Columns\TextColumn::make('statut')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'approuve' => 'success',
                        'en_attente' => 'warning',
                        'rejete' => 'danger',
                        'annule' => 'gray',
                        default => 'gray',
                    }),
                Tables\Columns\IconColumn::make('est_paye')
                    ->label('Payé')
                    ->boolean(),
                Tables\Columns\TextColumn::make('validateur.name')
                    ->label('Validé par')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('date_validation')
                    ->label('Date validation')
                    ->dateTime('d/m/Y H:i')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('statut')
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
                Tables\Actions\Action::make('approve')
                    ->label('Approuver')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->visible(fn ($record) => $record->statut === 'en_attente')
                    ->requiresConfirmation()
                    ->form([
                        Forms\Components\Textarea::make('commentaire')
                            ->label('Commentaire (optionnel)')
                            ->maxLength(255),
                    ])
                    ->action(function ($record, array $data) {
                        $record->update([
                            'statut' => 'approuve',
                            'validateur_id' => auth()->id(),
                            'date_validation' => now(),
                            'commentaire_validation' => $data['commentaire'] ?? null,
                        ]);
                    }),
                Tables\Actions\Action::make('reject')
                    ->label('Rejeter')
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->visible(fn ($record) => $record->statut === 'en_attente')
                    ->requiresConfirmation()
                    ->form([
                        Forms\Components\Textarea::make('commentaire')
                            ->label('Motif du rejet')
                            ->required()
                            ->maxLength(255),
                    ])
                    ->action(function ($record, array $data) {
                        $record->update([
                            'statut' => 'rejete',
                            'validateur_id' => auth()->id(),
                            'date_validation' => now(),
                            'commentaire_validation' => $data['commentaire'],
                        ]);
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('approveMultiple')
                        ->label('Approuver la sélection')
                        ->icon('heroicon-o-check')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function (array $records) {
                            foreach ($records as $record) {
                                if ($record->statut === 'en_attente') {
                                    $record->update([
                                        'statut' => 'approuve',
                                        'validateur_id' => auth()->id(),
                                        'date_validation' => now(),
                                    ]);
                                }
                            }
                        }),
                ]),
            ]);
    }
}
