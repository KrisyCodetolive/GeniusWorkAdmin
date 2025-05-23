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
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\IconPosition;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Navigation\NavigationItem;
use Filament\Notifications\Notification;
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
                Forms\Components\Tabs::make('Tabs')
                    ->tabs([
                        Forms\Components\Tabs\Tab::make('Informations employé')
                            ->icon('heroicon-o-user')
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
                                            ->disabled()
                                            ->helperText('Employé concerné par le retard ou l\'absence'),
                                        
                                        Forms\Components\Select::make('employeur_id')
                                            ->label('Employeur')
                                            ->relationship('employeur', 'nom')
                                            ->searchable()
                                            ->preload()
                                            ->required()
                                            ->disabled()
                                            ->helperText('Entreprise de l\'employé'),
                                    ]),
                            ]),
                        
                        Forms\Components\Tabs\Tab::make('Détails')
                            ->icon('heroicon-o-clipboard-document-list')
                            ->schema([
                                Forms\Components\Grid::make(2)
                                    ->schema([
                                        Forms\Components\DateTimePicker::make('date_heure_entree')
                                            ->label('Heure d\'arrivée')
                                            ->seconds(false)
                                            ->visible(fn (callable $get) => $get('statut') === 'retard')
                                            ->helperText('Heure d\'arrivée effective de l\'employé'),
                                        
                                        Forms\Components\TextInput::make('retard')
                                            ->label('Retard (minutes)')
                                            ->numeric()
                                            ->visible(fn (callable $get) => $get('statut') === 'retard')
                                            ->disabled()
                                            ->suffixIcon('heroicon-o-clock')
                                            ->helperText('Durée du retard en minutes'),
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
                                            ->disabled()
                                            ->helperText('Type d\'événement'),
                                    ]),
                            ]),
                        
                        Forms\Components\Tabs\Tab::make('Validation')
                            ->icon('heroicon-o-check-badge')
                            ->schema([
                                Forms\Components\Grid::make(1)
                                    ->schema([
                                        Forms\Components\Select::make('statut_validation')
                                            ->label('Statut de validation')
                                            ->options([
                                                'en_attente' => 'En attente',
                                                'approve' => 'Approuvé',
                                                'rejete' => 'Rejeté',
                                            ])
                                            ->default('en_attente')
                                            ->required()
                                            ->helperText('État actuel de la validation'),
                                    ]),
                                    
                                Forms\Components\Grid::make(2)
                                    ->schema([
                                        Forms\Components\Select::make('validateur_id')
                                            ->label('Validé par')
                                            ->relationship('validateur', 'nom')
                                            ->getOptionLabelFromRecordUsing(fn ($record) => $record->nom . ' ' . $record->prenom)
                                            ->searchable()
                                            ->preload()
                                            ->helperText('Personne ayant validé cette entrée'),
                                        
                                        Forms\Components\DateTimePicker::make('date_validation')
                                            ->label('Date de validation')
                                            ->seconds(false)
                                            ->helperText('Date et heure de la validation'),
                                    ]),
                                    
                                Forms\Components\Textarea::make('commentaire')
                                    ->label('Commentaire')
                                    ->maxLength(1000)
                                    ->columnSpanFull()
                                    ->helperText('Commentaires additionnels ou motif de rejet'),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->contentGrid([
                'md' => 2,
                'xl' => 3,
            ])
            ->columns([
                Tables\Columns\TextColumn::make('date_heure_entree')
                    ->label('Date')
                    ->dateTime()
                    ->sortable()
                    ->icon('heroicon-o-calendar')
                    ->weight(FontWeight::Bold),
                
                Tables\Columns\TextColumn::make('user.nom')
                    ->label('Employé')
                    ->formatStateUsing(fn ($record) => $record->user ? $record->user->nom . ' ' . $record->user->prenom : 'Non défini')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-o-user')
                    ->description(fn ($record) => $record->user ? $record->user->email : null),
                
                Tables\Columns\TextColumn::make('employeur.nom')
                    ->label('Employeur')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-o-building-office')
                    ->description(fn ($record) => $record->employeur ? $record->employeur->adresse : null),
                
                Tables\Columns\TextColumn::make('retard')
                    ->label('Retard')
                    ->numeric()
                    ->sortable()
                    ->formatStateUsing(fn ($state, $record) => $record->statut === 'retard' ? ($state ? $state . ' min' : '-') : '-')
                    ->visible(fn ($livewire) => $livewire->activeTab === 'retards' || $livewire->activeTab === null)
                    ->icon('heroicon-o-clock')
                    ->color('warning'),
                
                Tables\Columns\TextColumn::make('statut')
                    ->label('Type')
                    ->badge()
                    ->icon(fn (string $state): string => match ($state) {
                        'retard' => 'heroicon-o-clock',
                        'absent' => 'heroicon-o-x-circle',
                        default => 'heroicon-o-question-mark-circle',
                    })
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
                    ->icon(fn (string $state): string => match ($state) {
                        'en_attente' => 'heroicon-o-clock',
                        'approve' => 'heroicon-o-check-circle',
                        'rejete' => 'heroicon-o-x-circle',
                        default => 'heroicon-o-question-mark-circle',
                    })
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
                    ->toggleable()
                    ->icon('heroicon-o-user-circle'),
                
                Tables\Columns\TextColumn::make('date_validation')
                    ->label('Date validation')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->icon('heroicon-o-calendar-days'),
                
                Tables\Columns\TextColumn::make('commentaire')
                    ->label('Commentaire')
                    ->limit(30)
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->icon('heroicon-o-chat-bubble-left-ellipsis'),
            ])
            ->striped()
            ->filters([
                SelectFilter::make('statut')
                    ->label('Type')
                    ->options([
                        'retard' => 'Retard',
                        'absent' => 'Absence',
                    ])
                    ->indicator(true)
                    ->multiple()
                    ->preload(),
                
                SelectFilter::make('statut_validation')
                    ->label('Statut de validation')
                    ->options([
                        'en_attente' => 'En attente',
                        'approve' => 'Approuvé',
                        'rejete' => 'Rejeté',
                    ])
                    ->indicator(true)
                    ->multiple()
                    ->preload(),
                
                SelectFilter::make('employeur_id')
                    ->label('Employeur')
                    ->relationship('employeur', 'nom')
                    ->searchable()
                    ->preload()
                    ->indicator(true),
                
                Filter::make('date')
                    ->label('Période')
                    ->indicateUsing(function (array $data): ?string {
                        if (!$data['date_debut'] && !$data['date_fin']) {
                            return null;
                        }
                        
                        if ($data['date_debut'] && !$data['date_fin']) {
                            return 'Depuis le ' . Carbon::parse($data['date_debut'])->format('d/m/Y');
                        }
                        
                        if (!$data['date_debut'] && $data['date_fin']) {
                            return 'Jusqu\'au ' . Carbon::parse($data['date_fin'])->format('d/m/Y');
                        }
                        
                        return 'Du ' . Carbon::parse($data['date_debut'])->format('d/m/Y') . ' au ' . Carbon::parse($data['date_fin'])->format('d/m/Y');
                    })
                    ->form([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\DatePicker::make('date_debut')
                                    ->label('Date de début')
                                    ->placeholder('Depuis'),
                                Forms\Components\DatePicker::make('date_fin')
                                    ->label('Date de fin')
                                    ->placeholder('Jusqu\'au'),
                            ]),
                        Forms\Components\Select::make('preset')
                            ->label('Périodes prédéfinies')
                            ->options([
                                'today' => 'Aujourd\'hui',
                                'yesterday' => 'Hier',
                                'this_week' => 'Cette semaine',
                                'last_week' => 'Semaine dernière',
                                'this_month' => 'Ce mois-ci',
                                'last_month' => 'Mois dernier',
                            ])
                            ->afterStateUpdated(function ($state, callable $set) {
                                if ($state === 'today') {
                                    $set('date_debut', Carbon::today()->format('Y-m-d'));
                                    $set('date_fin', Carbon::today()->format('Y-m-d'));
                                } elseif ($state === 'yesterday') {
                                    $set('date_debut', Carbon::yesterday()->format('Y-m-d'));
                                    $set('date_fin', Carbon::yesterday()->format('Y-m-d'));
                                } elseif ($state === 'this_week') {
                                    $set('date_debut', Carbon::now()->startOfWeek()->format('Y-m-d'));
                                    $set('date_fin', Carbon::now()->endOfWeek()->format('Y-m-d'));
                                } elseif ($state === 'last_week') {
                                    $set('date_debut', Carbon::now()->subWeek()->startOfWeek()->format('Y-m-d'));
                                    $set('date_fin', Carbon::now()->subWeek()->endOfWeek()->format('Y-m-d'));
                                } elseif ($state === 'this_month') {
                                    $set('date_debut', Carbon::now()->startOfMonth()->format('Y-m-d'));
                                    $set('date_fin', Carbon::now()->endOfMonth()->format('Y-m-d'));
                                } elseif ($state === 'last_month') {
                                    $set('date_debut', Carbon::now()->subMonth()->startOfMonth()->format('Y-m-d'));
                                    $set('date_fin', Carbon::now()->subMonth()->endOfMonth()->format('Y-m-d'));
                                }
                            })
                            ->live()
                            ->placeholder('Sélectionner une période'),
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
                    )
                    ->indicator(true),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('valider')
                        ->label('Approuver')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->visible(fn (Presence $record) => $record->validateur_id === null)
                        ->requiresConfirmation()
                        ->modalHeading('Approuver cette entrée')
                        ->modalDescription('Confirmez-vous l\'approbation de cette entrée ? Cette action est irréversible.')
                        ->modalSubmitActionLabel('Oui, approuver')
                        ->successNotification(
                            Notification::make()
                                ->success()
                                ->title('Entrée approuvée')
                                ->body('L\'entrée a été approuvée avec succès.')
                        )
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
                        ->modalHeading('Rejeter cette entrée')
                        ->modalDescription('Veuillez indiquer le motif du rejet. Cette action est irréversible.')
                        ->modalSubmitActionLabel('Rejeter')
                        ->successNotification(
                            Notification::make()
                                ->warning()
                                ->title('Entrée rejetée')
                                ->body('L\'entrée a été rejetée.')
                        )
                        ->form([
                            Forms\Components\Textarea::make('commentaire')
                                ->label('Motif de rejet')
                                ->placeholder('Veuillez indiquer la raison du rejet...')
                                ->required()
                                ->minLength(10)
                                ->helperText('Minimum 10 caractères'),
                        ])
                        ->action(function (Presence $record, array $data) {
                            $record->update([
                                'validateur_id' => Auth::id(),
                                'date_validation' => Carbon::now(),
                                'statut_validation' => 'rejete',
                                'commentaire' => $data['commentaire']
                            ]);
                        }),
                    Tables\Actions\ViewAction::make()
                        ->label('Détails')
                        ->icon('heroicon-o-eye')
                        ->color('gray'),
                ])
                ->label('Actions')
                ->icon('heroicon-m-ellipsis-vertical')
                ->size('sm')
                ->color('gray')
                ->button(),
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
            ->defaultGroup('statut');
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
