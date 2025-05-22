<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CongeResource\Pages;
use App\Filament\Resources\CongeResource\RelationManagers;
use App\Models\Conge;
use App\Models\TypeConge;
use App\Models\User;
use App\Services\CongeService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Colors\Color;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class CongeResource extends Resource
{
    protected static ?string $model = Conge::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar';
    
    protected static ?string $navigationLabel = 'Demandes de congés';
    
    protected static ?int $navigationSort = 1;
    
    protected static ?string $recordTitleAttribute = 'motif';

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('statut', 'en_attente')->count() ?: null;
    }
    
    public static function getNavigationBadgeColor(): string
    {
        return static::getModel()::where('statut', 'en_attente')->count() > 0
            ? 'warning'
            : 'primary';
    }

    public static function form(Form $form): Form
    {
        $user = auth()->user();
        $isSuperAdminOrSupport = $user->isSuperAdmin() || $user->isSupport();
        
        return $form
            ->schema([
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Informations de la demande')
                            ->description('Informations principales de la demande de congé')
                            ->schema([
                                Forms\Components\Select::make('employeur_id')
                                    ->label('Employé')
                                    ->relationship(
                                        'employeur',
                                        'nom',
                                        function (Builder $query) use ($user, $isSuperAdminOrSupport) {
                                            // Si l'utilisateur n'est pas SuperAdmin ou Support, filtrer par entreprise
                                            if (!$isSuperAdminOrSupport) {
                                                return $query->where('entreprise_id', $user->entreprise_id);
                                            }
                                            return $query;
                                        }
                                    )
                                    ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->prenom} {$record->nom}")
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->disabled(fn () => !$user->isAdmin() && !$isSuperAdminOrSupport),
                                Forms\Components\Select::make('type_conge_id')
                                    ->label('Type de congé')
                                    ->relationship('typeConge', 'nom', function (Builder $query) use ($user, $isSuperAdminOrSupport) {
                                        // Si l'utilisateur n'est pas SuperAdmin ou Support, filtrer par entreprise
                                        if (!$isSuperAdminOrSupport) {
                                            return $query->where('entreprise_id', $user->entreprise_id);
                                        }
                                        return $query;
                                    })
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, callable $set) {
                                        if ($state) {
                                            $typeConge = TypeConge::find($state);
                                            $set('est_paye', $typeConge->est_paye);
                                            $set('necessite_justificatif', $typeConge->necessite_justificatif);
                                        }
                                    }),
                                Forms\Components\Grid::make(2)
                                    ->schema([
                                        Forms\Components\DatePicker::make('date_debut')
                                            ->label('Date de début')
                                            ->required()
                                            ->reactive()
                                            ->afterStateUpdated(function ($state, callable $set, $get) {
                                                if ($state && $get('date_fin')) {
                                                    $debut = Carbon::parse($state);
                                                    $fin = Carbon::parse($get('date_fin'));
                                                    
                                                    // Calculer les jours ouvrables
                                                    $joursOuvrables = 0;
                                                    $date = clone $debut;
                                                    
                                                    while ($date->lte($fin)) {
                                                        if (!in_array($date->dayOfWeek, [0, 6])) { // Exclure samedi et dimanche
                                                            $joursOuvrables++;
                                                        }
                                                        $date->addDay();
                                                    }
                                                    
                                                    $set('duree_jours', $joursOuvrables);
                                                }
                                            }),
                                        Forms\Components\DatePicker::make('date_fin')
                                            ->label('Date de fin')
                                            ->required()
                                            ->after('date_debut')
                                            ->reactive()
                                            ->afterStateUpdated(function ($state, callable $set, $get) {
                                                if ($state && $get('date_debut')) {
                                                    $debut = Carbon::parse($get('date_debut'));
                                                    $fin = Carbon::parse($state);
                                                    
                                                    // Calculer les jours ouvrables
                                                    $joursOuvrables = 0;
                                                    $date = clone $debut;
                                                    
                                                    while ($date->lte($fin)) {
                                                        if (!in_array($date->dayOfWeek, [0, 6])) { // Exclure samedi et dimanche
                                                            $joursOuvrables++;
                                                        }
                                                        $date->addDay();
                                                    }
                                                    
                                                    $set('duree_jours', $joursOuvrables);
                                                }
                                            }),
                                    ]),
                                Forms\Components\TextInput::make('duree_jours')
                                    ->label('Durée (jours ouvrables)')
                                    ->required()
                                    ->numeric()
                                    ->step(0.5)
                                    ->disabled(),
                                Forms\Components\Textarea::make('motif')
                                    ->label('Motif de la demande')
                                    ->placeholder('Précisez le motif de votre demande de congé')
                                    ->maxLength(255),
                                Forms\Components\Toggle::make('necessite_justificatif')
                                    ->label('Justificatif requis')
                                    ->disabled()
                                    ->dehydrated(false),
                                Forms\Components\FileUpload::make('justificatif')
                                    ->label('Justificatif')
                                    ->directory('justificatifs/conges')
                                    ->visibility('public')
                                    ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png'])
                                    ->visible(fn ($get) => $get('necessite_justificatif')),
                            ])
                            ->columns(2),
                    ])
                    ->columnSpan(['lg' => 2]),
                
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Statut et validation')
                            ->description('Informations sur la validation de la demande')
                            ->schema([
                                Forms\Components\Select::make('statut')
                                    ->options([
                                        'en_attente' => 'En attente',
                                        'approuve' => 'Approuvé',
                                        'rejete' => 'Rejeté',
                                        'annule' => 'Annulé',
                                    ])
                                    ->default('en_attente')
                                    ->required()
                                    ->disabled(fn () => !$user->isAdmin() && !$isSuperAdminOrSupport),
                                Forms\Components\Select::make('validateur_id')
                                    ->label('Validé par')
                                    ->relationship('validateur', 'name', function (Builder $query) use ($user, $isSuperAdminOrSupport) {
                                        // Si l'utilisateur n'est pas SuperAdmin ou Support, filtrer par entreprise
                                        if (!$isSuperAdminOrSupport) {
                                            return $query->where('entreprise_id', $user->entreprise_id);
                                        }
                                        return $query;
                                    })
                                    ->searchable()
                                    ->preload()
                                    ->disabled(fn () => !$user->isAdmin() && !$isSuperAdminOrSupport),
                                Forms\Components\Hidden::make('date_validation')
                                    ->dehydrateStateUsing(fn () => now())
                                    ->hiddenOn('view'),
                                Forms\Components\Textarea::make('commentaire_validation')
                                    ->label('Commentaire de validation')
                                    ->placeholder('Commentaires sur la décision')
                                    ->maxLength(255)
                                    ->disabled(fn () => !$user->isAdmin() && !$isSuperAdminOrSupport),
                                Forms\Components\Toggle::make('est_paye')
                                    ->label('Congé payé')
                                    ->helperText('L\'employé est rémunéré pendant ce congé')
                                    ->required()
                                    ->disabled(fn () => !$user->isAdmin() && !$isSuperAdminOrSupport),
                            ]),
                        
                        Forms\Components\Section::make('Métadonnées')
                            ->schema([
                                Forms\Components\KeyValue::make('meta_donnees')
                                    ->label('Informations supplémentaires')
                                    ->keyLabel('Clé')
                                    ->valueLabel('Valeur')
                                    ->disabled()
                                    ->visible(fn () => $user->isAdmin() || $isSuperAdminOrSupport),
                                Forms\Components\Placeholder::make('created_at')
                                    ->label('Créé le')
                                    ->content(fn (Conge $record = null): ?string => $record?->created_at?->format('d/m/Y H:i')),
                                Forms\Components\Placeholder::make('updated_at')
                                    ->label('Dernière modification')
                                    ->content(fn (Conge $record = null): ?string => $record?->updated_at?->format('d/m/Y H:i')),
                            ])
                            ->collapsed(),
                    ])
                    ->columnSpan(['lg' => 1]),
            ])
            ->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('employeur.nom')
                    ->label('Employé')
                    ->formatStateUsing(fn ($record) => $record->employeur->prenom . ' ' . $record->employeur->nom)
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('typeConge.nom')
                    ->label('Type de congé')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
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
                    ->label('Motif')
                    ->limit(30)
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\IconColumn::make('justificatif')
                    ->label('Justificatif')
                    ->boolean()
                    ->getStateUsing(fn (Conge $record): bool => !empty($record->justificatif))
                    ->toggleable(),
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
                    ->boolean()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('validateur.name')
                    ->label('Validé par')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('date_validation')
                    ->label('Date validation')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
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
                Tables\Filters\SelectFilter::make('type_conge_id')
                    ->label('Type de congé')
                    ->relationship('typeConge', 'nom')
                    ->searchable()
                    ->preload(),
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
                Tables\Filters\TernaryFilter::make('est_paye')
                    ->label('Congé payé'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('approve')
                    ->label('Approuver')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->visible(fn ($record) => $record->statut === 'en_attente' && auth()->user()->isAdmin())
                    ->requiresConfirmation()
                    ->form([
                        Forms\Components\Textarea::make('commentaire')
                            ->label('Commentaire (optionnel)')
                            ->maxLength(255),
                    ])
                    ->action(function (Conge $record, array $data) {
                        $congeService = app(CongeService::class);
                        $congeService->approuverDemande($record, auth()->user(), $data['commentaire'] ?? null);
                    }),
                Tables\Actions\Action::make('reject')
                    ->label('Rejeter')
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->visible(fn ($record) => $record->statut === 'en_attente' && auth()->user()->isAdmin())
                    ->requiresConfirmation()
                    ->form([
                        Forms\Components\Textarea::make('commentaire')
                            ->label('Motif du rejet')
                            ->required()
                            ->maxLength(255),
                    ])
                    ->action(function (Conge $record, array $data) {
                        $congeService = app(CongeService::class);
                        $congeService->rejeterDemande($record, auth()->user(), $data['commentaire']);
                    }),
                Tables\Actions\Action::make('cancel')
                    ->label('Annuler')
                    ->icon('heroicon-o-trash')
                    ->color('gray')
                    ->visible(fn ($record) => ($record->statut === 'en_attente' || $record->statut === 'approuve') && 
                        (auth()->user()->isAdmin() || auth()->id() === $record->employeur->user_id))
                    ->requiresConfirmation()
                    ->form([
                        Forms\Components\Textarea::make('commentaire')
                            ->label('Motif de l\'annulation (optionnel)')
                            ->maxLength(255),
                    ])
                    ->action(function (Conge $record, array $data) {
                        $congeService = app(CongeService::class);
                        $congeService->annulerDemande($record, $data['commentaire'] ?? null);
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn () => auth()->user()->isAdmin()),
                    Tables\Actions\BulkAction::make('approveMultiple')
                        ->label('Approuver la sélection')
                        ->icon('heroicon-o-check')
                        ->color('success')
                        ->visible(fn () => auth()->user()->isAdmin())
                        ->requiresConfirmation()
                        ->action(function (array $records) {
                            $congeService = app(CongeService::class);
                            foreach ($records as $record) {
                                if ($record->statut === 'en_attente') {
                                    $congeService->approuverDemande($record, auth()->user());
                                }
                            }
                        }),
                    Tables\Actions\BulkAction::make('rejectMultiple')
                        ->label('Rejeter la sélection')
                        ->icon('heroicon-o-x-mark')
                        ->color('danger')
                        ->visible(fn () => auth()->user()->isAdmin())
                        ->requiresConfirmation()
                        ->form([
                            Forms\Components\Textarea::make('commentaire')
                                ->label('Motif du rejet')
                                ->required()
                                ->maxLength(255),
                        ])
                        ->action(function (array $records, array $data) {
                            $congeService = app(CongeService::class);
                            foreach ($records as $record) {
                                if ($record->statut === 'en_attente') {
                                    $congeService->rejeterDemande($record, auth()->user(), $data['commentaire']);
                                }
                            }
                        }),
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
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
            'index' => Pages\ListConges::route('/'),
            'create' => Pages\CreateConge::route('/create'),
            'view' => Pages\ViewConge::route('/{record}'),
            'edit' => Pages\EditConge::route('/{record}/edit'),
        ];
    }
    
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        
        // Si l'utilisateur n'est pas admin, filtrer les congés par employeur
        if (!auth()->user()->isAdmin()) {
            // Si l'utilisateur est manager, montrer les congés des employés de son département/entreprise
            if (auth()->user()->isManager()) {
                // Logique pour récupérer les employés sous la responsabilité du manager
                // À adapter selon votre modèle de données
                $query->whereHas('employeur', function ($query) {
                    $query->where('entreprise_id', auth()->user()->entreprise_id);
                });
            } else {
                // Utilisateur standard, ne voir que ses propres congés
                $query->whereHas('employeur', function ($query) {
                    $query->where('user_id', auth()->id());
                });
            }
        }
        
        // Trier les congés par ordre du plus récent au plus ancien
        return $query->orderBy('created_at', 'desc');
    }
}
