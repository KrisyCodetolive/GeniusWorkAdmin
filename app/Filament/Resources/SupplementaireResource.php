<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SupplementaireResource\Pages;
use App\Models\Supplementaire;
use App\Models\User;
use App\Models\Employeur;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\TernaryFilter;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class SupplementaireResource extends Resource
{
    protected static ?string $model = Supplementaire::class;

    protected static ?string $navigationIcon = 'heroicon-o-clock';
    
    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $recordTitleAttribute = 'id';
    
    protected static ?string $modelLabel = 'Heure Supplémentaire';
    
    protected static ?string $pluralModelLabel = 'Heures Supplémentaires';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Informations générales')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('employeur_id')
                                    ->label('Employé')
                                    ->relationship('employeur', 'nom')
                                    ->searchable()
                                    ->preload()
                                    ->required(),
                                
                                DatePicker::make('date')
                                    ->label('Date')
                                    ->required(),
                            ]),
                            
                        Grid::make(2)
                            ->schema([
                                TimePicker::make('heure_debut')
                                    ->label('Heure de début')
                                    ->seconds(false)
                                    ->required(),
                                
                                TimePicker::make('heure_fin')
                                    ->label('Heure de fin')
                                    ->seconds(false)
                                    ->required(),
                            ]),
                            
                        Grid::make(3)
                            ->schema([
                                TextInput::make('nombre_heures')
                                    ->label('Nombre d\'heures')
                                    ->numeric()
                                    ->step(0.01)
                                    ->required(),
                                
                                TextInput::make('taux_majoration')
                                    ->label('Taux de majoration (%)')
                                    ->numeric()
                                    ->step(0.01)
                                    ->default(50)
                                    ->required(),
                                
                                TextInput::make('montant')
                                    ->label('Montant')
                                    ->numeric()
                                    ->step(0.01)
                                    ->disabled(),
                            ]),
                            
                        Grid::make(1)
                            ->schema([
                                Textarea::make('motif')
                                    ->label('Motif')
                                    ->required()
                                    ->maxLength(500),
                            ]),
                    ]),
                    
                Section::make('Validation')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('statut')
                                    ->label('Statut')
                                    ->options([
                                        'en_attente' => 'En attente',
                                        'approuve' => 'Approuvé',
                                        'rejete' => 'Rejeté',
                                        'annule' => 'Annulé',
                                    ])
                                    ->default('en_attente')
                                    ->required(),
                                
                                Select::make('validateur_id')
                                    ->label('Validé par')
                                    ->relationship('validateur', 'nom')
                                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->nom . ' ' . $record->prenom)
                                    ->searchable()
                                    ->preload(),
                            ]),
                            
                        Grid::make(2)
                            ->schema([
                                Forms\Components\DateTimePicker::make('date_validation')
                                    ->label('Date de validation')
                                    ->seconds(false),
                                
                                Textarea::make('commentaire')
                                    ->label('Commentaire')
                                    ->maxLength(500),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                Tables\Columns\TextColumn::make('date')
                    ->label('Date')
                    ->date()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('employeur.nom')
                    ->label('Employé')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('heure_debut')
                    ->label('Début')
                    ->time()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('heure_fin')
                    ->label('Fin')
                    ->time()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('nombre_heures')
                    ->label('Heures')
                    ->numeric(2)
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('taux_majoration')
                    ->label('Taux (%)')
                    ->numeric(2)
                    ->sortable()
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('montant')
                    ->label('Montant')
                    ->money(fn ($record) => $record->employeur->entreprise->devise ?? 'XOF')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('statut')
                    ->label('Statut')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'en_attente' => 'En attente',
                        'approuve' => 'Approuvé',
                        'rejete' => 'Rejeté',
                        'annule' => 'Annulé',
                        default => $state,
                    })
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'en_attente' => 'gray',
                        'approuve' => 'success',
                        'rejete' => 'danger',
                        'annule' => 'warning',
                        default => 'gray',
                    })
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('validateur.nom')
                    ->label('Validé par')
                    ->formatStateUsing(fn ($record) => $record->validateur ? $record->validateur->nom . ' ' . $record->validateur->prenom : '-')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('date_validation')
                    ->label('Date validation')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                Tables\Columns\TextColumn::make('motif')
                    ->label('Motif')
                    ->limit(30)
                    ->searchable()
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('commentaire')
                    ->label('Commentaire')
                    ->limit(30)
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Mis à jour le')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('statut')
                    ->label('Statut')
                    ->options([
                        'en_attente' => 'En attente',
                        'approuve' => 'Approuvé',
                        'rejete' => 'Rejeté',
                        'annule' => 'Annulé',
                    ]),
                
                SelectFilter::make('employeur_id')
                    ->label('Employé')
                    ->relationship('employeur', 'nom'),
                
                Filter::make('date')
                    ->label('Période')
                    ->form([
                        Forms\Components\DatePicker::make('date_debut')
                            ->label('Date de début'),
                        Forms\Components\DatePicker::make('date_fin')
                            ->label('Date de fin'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['date_debut'],
                                fn (Builder $query, $date): Builder => $query->whereDate('date', '>=', $date),
                            )
                            ->when(
                                $data['date_fin'],
                                fn (Builder $query, $date): Builder => $query->whereDate('date', '<=', $date),
                            );
                    }),
                
                TernaryFilter::make('validation')
                    ->label('Validation')
                    ->placeholder('Tous')
                    ->trueLabel('Validés')
                    ->falseLabel('Non validés')
                    ->queries(
                        true: fn (Builder $query) => $query->whereNotNull('validateur_id'),
                        false: fn (Builder $query) => $query->whereNull('validateur_id'),
                        blank: fn (Builder $query) => $query,
                    ),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\Action::make('valider')
                    ->label('Approuver')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (Supplementaire $record) => $record->statut === 'en_attente')
                    ->action(function (Supplementaire $record) {
                        $record->valider(User::find(Auth::id()));
                    }),
                Tables\Actions\Action::make('rejeter')
                    ->label('Rejeter')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (Supplementaire $record) => $record->statut === 'en_attente')
                    ->form([
                        Forms\Components\Textarea::make('commentaire')
                            ->label('Motif de rejet')
                            ->required(),
                    ])
                    ->action(function (Supplementaire $record, array $data) {
                        $record->rejeter(User::find(Auth::id()), $data['commentaire']);
                    }),
                Tables\Actions\Action::make('annuler')
                    ->label('Annuler')
                    ->icon('heroicon-o-x-mark')
                    ->color('warning')
                    ->visible(fn (Supplementaire $record) => $record->statut === 'approuve')
                    ->form([
                        Forms\Components\Textarea::make('commentaire')
                            ->label('Motif d\'annulation')
                            ->required(),
                    ])
                    ->action(function (Supplementaire $record, array $data) {
                        $record->annuler($data['commentaire']);
                    }),
                Tables\Actions\Action::make('calculer')
                    ->label('Calculer montant')
                    ->icon('heroicon-o-calculator')
                    ->color('gray')
                    ->action(function (Supplementaire $record) {
                        $record->calculerMontant();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('approuverMultiple')
                        ->label('Approuver la sélection')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(function (Collection $records) {
                            foreach ($records as $record) {
                                if ($record->statut === 'en_attente') {
                                    $record->valider(User::find(Auth::id()));
                                }
                            }
                        }),
                    Tables\Actions\BulkAction::make('rejeterMultiple')
                        ->label('Rejeter la sélection')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->form([
                            Forms\Components\Textarea::make('commentaire')
                                ->label('Motif de rejet')
                                ->required(),
                        ])
                        ->action(function (Collection $records, array $data) {
                            foreach ($records as $record) {
                                if ($record->statut === 'en_attente') {
                                    $record->rejeter(User::find(Auth::id()), $data['commentaire']);
                                }
                            }
                        }),
                    Tables\Actions\BulkAction::make('calculerMultiple')
                        ->label('Calculer les montants')
                        ->icon('heroicon-o-calculator')
                        ->color('gray')
                        ->action(function (Collection $records) {
                            foreach ($records as $record) {
                                $record->calculerMontant();
                            }
                        }),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSupplementaires::route('/'),
            'create' => Pages\CreateSupplementaire::route('/create'),
            'edit' => Pages\EditSupplementaire::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
