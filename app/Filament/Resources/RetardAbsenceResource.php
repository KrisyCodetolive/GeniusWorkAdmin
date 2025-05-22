<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RetardAbsenceResource\Pages;
use App\Models\Presence;
use App\Models\User;
use App\Models\Employeur;
use App\Services\RetardAbsenceService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\TernaryFilter;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use App\Filament\Widgets\RetardAbsenceStatsWidget;

class RetardAbsenceResource extends Resource
{
    protected static ?string $model = Presence::class;

    protected static ?string $navigationIcon = 'heroicon-o-exclamation-circle';
    
    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $recordTitleAttribute = 'id';
    
    protected static ?string $modelLabel = 'Retard & Absence';
    
    protected static ?string $pluralModelLabel = 'Retards & Absences';
    
    protected static ?string $slug = 'retards-absences';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informations')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Select::make('user_id')
                                    ->label('Employé')
                                    ->relationship('user', 'nom')
                                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->nom . ' ' . $record->prenom)
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->disabled(),
                                
                                Forms\Components\Select::make('employeur_id')
                                    ->label('Employeur')
                                    ->relationship('employeur', 'nom')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->disabled(),
                            ]),
                            
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\DateTimePicker::make('date_heure_entree')
                                    ->label('Heure d\'arrivée')
                                    ->seconds(false)
                                    ->visible(fn (callable $get) => $get('statut') === 'retard'),
                                
                                Forms\Components\TextInput::make('retard')
                                    ->label('Retard (minutes)')
                                    ->numeric()
                                    ->visible(fn (callable $get) => $get('statut') === 'retard')
                                    ->disabled(),
                            ]),
                            
                        Forms\Components\Grid::make(1)
                            ->schema([
                                Forms\Components\Select::make('statut')
                                    ->label('Type')
                                    ->options([
                                        'retard' => 'Retard',
                                        'absent' => 'Absence',
                                    ])
                                    ->required()
                                    ->disabled(),
                                
                                Forms\Components\Select::make('statut_validation')
                                    ->label('Statut de validation')
                                    ->options([
                                        'en_attente' => 'En attente',
                                        'approve' => 'Approuvé',
                                        'rejete' => 'Rejeté',
                                    ])
                                    ->default('en_attente')
                                    ->required(),
                            ]),
                            
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Select::make('validateur_id')
                                    ->label('Validé par')
                                    ->relationship('validateur', 'nom')
                                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->nom . ' ' . $record->prenom)
                                    ->searchable()
                                    ->preload(),
                                
                                Forms\Components\DateTimePicker::make('date_validation')
                                    ->label('Date de validation')
                                    ->seconds(false),
                            ]),
                            
                        Forms\Components\Textarea::make('commentaire')
                            ->label('Commentaire')
                            ->maxLength(1000)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('date_heure_entree')
                    ->label('Date')
                    ->dateTime()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('user.nom')
                    ->label('Employé')
                    ->formatStateUsing(fn ($record) => $record->user ? $record->user->nom . ' ' . $record->user->prenom : 'Non défini')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('employeur.nom')
                    ->label('Employeur')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('retard')
                    ->label('Retard (min)')
                    ->numeric()
                    ->sortable()
                    ->formatStateUsing(fn ($state, $record) => $record->statut === 'retard' ? ($state ? $state . ' min' : '-') : '-')
                    ->visible(fn ($livewire) => $livewire->activeTab === 'retards' || $livewire->activeTab === null),
                
                Tables\Columns\TextColumn::make('statut')
                    ->label('Type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'retard' => 'warning',
                        'absent' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'retard' => 'Retard',
                        'absent' => 'Absence',
                        default => $state,
                    })
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('statut_validation')
                    ->label('Validation')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'en_attente' => 'En attente',
                        'approve' => 'Approuvé',
                        'rejete' => 'Rejeté',
                        default => $state,
                    })
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'en_attente' => 'gray',
                        'approve' => 'success',
                        'rejete' => 'danger',
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
                
                Tables\Columns\TextColumn::make('commentaire')
                    ->label('Commentaire')
                    ->limit(30)
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('statut')
                    ->label('Type')
                    ->options([
                        'retard' => 'Retard',
                        'absent' => 'Absence',
                    ]),
                
                SelectFilter::make('statut_validation')
                    ->label('Statut de validation')
                    ->options([
                        'en_attente' => 'En attente',
                        'approve' => 'Approuvé',
                        'rejete' => 'Rejeté',
                    ]),
                
                SelectFilter::make('employeur_id')
                    ->label('Employeur')
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
                                fn (Builder $query, $date): Builder => $query->whereDate('date_heure_entree', '>=', $date),
                            )
                            ->when(
                                $data['date_fin'],
                                fn (Builder $query, $date): Builder => $query->whereDate('date_heure_entree', '<=', $date),
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
                Tables\Actions\Action::make('valider')
                    ->label('Approuver')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (Presence $record) => $record->validateur_id === null)
                    ->action(function (Presence $record) {
                        $record->update([
                            'validateur_id' => Auth::id(),
                            'date_validation' => Carbon::now(),
                            'statut_validation' => 'approve'
                        ]);
                    }),
                Tables\Actions\Action::make('rejeter')
                    ->label('Rejeter')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (Presence $record) => $record->validateur_id === null)
                    ->form([
                        Forms\Components\Textarea::make('commentaire')
                            ->label('Motif de rejet')
                            ->required(),
                    ])
                    ->action(function (Presence $record, array $data) {
                        $record->update([
                            'validateur_id' => Auth::id(),
                            'date_validation' => Carbon::now(),
                            'statut_validation' => 'rejete',
                            'commentaire' => $data['commentaire']
                        ]);
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('validerMultiple')
                        ->label('Approuver la sélection')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(function (Collection $records) {
                            foreach ($records as $record) {
                                if ($record->validateur_id === null) {
                                    $record->update([
                                        'validateur_id' => Auth::id(),
                                        'date_validation' => Carbon::now(),
                                        'statut_validation' => 'approve'
                                    ]);
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
                                if ($record->validateur_id === null) {
                                    $record->update([
                                        'validateur_id' => Auth::id(),
                                        'date_validation' => Carbon::now(),
                                        'statut_validation' => 'rejete',
                                        'commentaire' => $data['commentaire']
                                    ]);
                                }
                            }
                        }),
                ]),
            ])
            ->filtersFormColumns(2)
            ->persistFiltersInSession()
            ->defaultPaginationPageOption(25)
            ->filtersTriggerAction(
                fn (Tables\Actions\Action $action) => $action
                    ->button()
                    ->label('Filtres')
                    ->icon('heroicon-o-funnel')
            )
            ->groups([
                'statut',
                'statut_validation',
                'employeur_id',
            ])
            ->defaultGroup('statut')
            ->paginated([10, 25, 50, 100]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getWidgets(): array
    {
        return [
            RetardAbsenceStatsWidget::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRetardAbsences::route('/'),
            'edit' => Pages\EditRetardAbsence::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereIn('statut', ['retard', 'absent'])
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
