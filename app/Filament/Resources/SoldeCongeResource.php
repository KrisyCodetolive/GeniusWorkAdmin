<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SoldeCongeResource\Pages;
use App\Filament\Resources\SoldeCongeResource\RelationManagers;
use App\Models\SoldeConge;
use App\Models\TypeConge;
use App\Models\User;
use App\Traits\HasEntrepriseScope;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Colors\Color;
use Carbon\Carbon;

class SoldeCongeResource extends Resource
{
    use HasEntrepriseScope;
    
    protected static ?string $model = SoldeConge::class;

    protected static ?string $navigationIcon = 'heroicon-o-calculator';
    
    // Caché dans le menu de navigation mais accessible via CongeResource
    protected static bool $shouldRegisterNavigation = false;
    
    protected static ?string $navigationLabel = 'Soldes de congés';
    
    protected static ?string $recordTitleAttribute = 'id';
    
    protected static ?string $modelLabel = 'Solde de congé';
    
    protected static ?string $pluralModelLabel = 'Soldes de congés';

    public static function form(Form $form): Form
    {
        $user = auth()->user();
        $isSuperAdminOrSupport = $user->isSuperAdmin() || $user->isSupport();
        
        return $form
            ->schema([
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Informations générales')
                            ->description('Informations de base du solde de congé')
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
                                    ->required(),
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
                                    ->required(),
                                Forms\Components\TextInput::make('annee')
                                    ->label('Année')
                                    ->required()
                                    ->numeric()
                                    ->default(date('Y'))
                                    ->minValue(2000)
                                    ->maxValue(2100),
                            ])
                            ->columns(3),
                        
                        Forms\Components\Section::make('Détails du solde')
                            ->description('Informations détaillées sur le solde de congé')
                            ->schema([
                                Forms\Components\Grid::make(2)
                                    ->schema([
                                        Forms\Components\TextInput::make('solde_initial')
                                            ->label('Solde initial')
                                            ->required()
                                            ->numeric()
                                            ->step(0.5)
                                            ->default(0.00)
                                            ->suffix('jours')
                                            ->helperText('Solde disponible en début d\'année')
                                            ->live()
                                            ->afterStateUpdated(fn (callable $set, $state, $get) => 
                                                $set('solde_restant', ($state ?? 0) + ($get('solde_acquis') ?? 0) - ($get('solde_pris') ?? 0))
                                            ),
                                        Forms\Components\TextInput::make('solde_acquis')
                                            ->label('Solde acquis')
                                            ->required()
                                            ->numeric()
                                            ->step(0.5)
                                            ->default(0.00)
                                            ->suffix('jours')
                                            ->helperText('Solde acquis au cours de l\'année')
                                            ->live()
                                            ->afterStateUpdated(fn (callable $set, $state, $get) => 
                                                $set('solde_restant', ($get('solde_initial') ?? 0) + ($state ?? 0) - ($get('solde_pris') ?? 0))
                                            ),
                                    ]),
                                Forms\Components\Grid::make(2)
                                    ->schema([
                                        Forms\Components\TextInput::make('solde_pris')
                                            ->label('Solde pris')
                                            ->required()
                                            ->numeric()
                                            ->step(0.5)
                                            ->default(0.00)
                                            ->suffix('jours')
                                            ->helperText('Congés pris durant l\'année')
                                            ->live()
                                            ->afterStateUpdated(fn (callable $set, $state, $get) => 
                                                $set('solde_restant', ($get('solde_initial') ?? 0) + ($get('solde_acquis') ?? 0) - ($state ?? 0))
                                            ),
                                        Forms\Components\TextInput::make('solde_restant')
                                            ->label('Solde restant')
                                            ->required()
                                            ->numeric()
                                            ->step(0.5)
                                            ->default(0.00)
                                            ->suffix('jours')
                                            ->helperText('Solde disponible actuellement')
                                            ->disabled()
                                            ->dehydrated()
                                            ->reactive()
                                            ->afterStateHydrated(function ($state, callable $set, $get) {
                                                $total = ($get('solde_initial') ?? 0) + ($get('solde_acquis') ?? 0) - ($get('solde_pris') ?? 0);
                                                $set('solde_restant', $total);
                                            }),
                                    ]),
                                Forms\Components\DateTimePicker::make('date_derniere_maj')
                                    ->label('Dernière mise à jour')
                                    ->default(now())
                                    ->required(),
                                Forms\Components\Textarea::make('commentaire')
                                    ->label('Commentaire')
                                    ->placeholder('Informations complémentaires sur ce solde')
                                    ->maxLength(255),
                            ]),
                    ])
                    ->columnSpan(['lg' => 2]),
                
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Actions')
                            ->schema([
                                Forms\Components\Placeholder::make('ajuster_placeholder')
                                    ->label('Ajuster le solde')
                                    ->content('Utilisez les actions pour ajuster le solde'),
                                Forms\Components\Actions::make([
                                    Forms\Components\Actions\Action::make('ajouter')
                                        ->label('Ajouter des jours')
                                        ->icon('heroicon-o-plus')
                                        ->color('success')
                                        ->form([
                                            Forms\Components\TextInput::make('jours')
                                                ->label('Nombre de jours')
                                                ->required()
                                                ->numeric()
                                                ->step(0.5)
                                                ->minValue(0.5),
                                            Forms\Components\Textarea::make('motif')
                                                ->label('Motif')
                                                ->required()
                                                ->maxLength(255),
                                        ])
                                        ->action(function (array $data, ?SoldeConge $record) {
                                            if ($record) {
                                                $record->ajouterSolde($data['jours'], $data['motif']);
                                            }
                                        })
                                        ->visible(fn (?SoldeConge $record) => $record && $record->exists),
                                    Forms\Components\Actions\Action::make('deduire')
                                        ->label('Déduire des jours')
                                        ->icon('heroicon-o-minus')
                                        ->color('danger')
                                        ->form([
                                            Forms\Components\TextInput::make('jours')
                                                ->label('Nombre de jours')
                                                ->required()
                                                ->numeric()
                                                ->step(0.5)
                                                ->minValue(0.5),
                                            Forms\Components\Textarea::make('motif')
                                                ->label('Motif')
                                                ->required()
                                                ->maxLength(255),
                                        ])
                                        ->action(function (array $data, ?SoldeConge $record) {
                                            if ($record) {
                                                $record->deduireSolde($data['jours'], $data['motif']);
                                            }
                                        })
                                        ->visible(fn (?SoldeConge $record) => $record && $record->exists),
                                ]),
                            ]),
                        
                        Forms\Components\Section::make('Métadonnées')
                            ->schema([
                                Forms\Components\KeyValue::make('meta_donnees')
                                    ->label('Informations supplémentaires')
                                    ->keyLabel('Clé')
                                    ->valueLabel('Valeur'),
                                Forms\Components\Placeholder::make('created_at')
                                    ->label('Créé le')
                                    ->content(fn (?SoldeConge $record): ?string => $record?->created_at?->format('d/m/Y H:i')),
                                Forms\Components\Placeholder::make('updated_at')
                                    ->label('Dernière modification')
                                    ->content(fn (?SoldeConge $record): ?string => $record?->updated_at?->format('d/m/Y H:i')),
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
                    ->sortable(),
                Tables\Columns\TextColumn::make('typeConge.nom')
                    ->label('Type de congé')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('annee')
                    ->label('Année')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('solde_initial')
                    ->label('Initial')
                    ->numeric(
                        decimalPlaces: 1,
                        decimalSeparator: ',',
                        thousandsSeparator: ' ',
                    )
                    ->suffix(' j')
                    ->sortable(),
                Tables\Columns\TextColumn::make('solde_acquis')
                    ->label('Acquis')
                    ->numeric(
                        decimalPlaces: 1,
                        decimalSeparator: ',',
                        thousandsSeparator: ' ',
                    )
                    ->suffix(' j')
                    ->sortable(),
                Tables\Columns\TextColumn::make('solde_pris')
                    ->label('Pris')
                    ->numeric(
                        decimalPlaces: 1,
                        decimalSeparator: ',',
                        thousandsSeparator: ' ',
                    )
                    ->suffix(' j')
                    ->sortable(),
                Tables\Columns\TextColumn::make('solde_restant')
                    ->label('Restant')
                    ->numeric(
                        decimalPlaces: 1,
                        decimalSeparator: ',',
                        thousandsSeparator: ' ',
                    )
                    ->suffix(' j')
                    ->sortable()
                    ->weight(FontWeight::Bold)
                    ->color(fn ($state): string => 
                        $state <= 0 ? 'danger' : 
                        ($state < 5 ? 'warning' : 'success')
                    ),
                Tables\Columns\TextColumn::make('date_derniere_maj')
                    ->label('Dernière MAJ')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('employeur_id')
                    ->label('Employé')
                    ->relationship('employeur', 'nom')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('type_conge_id')
                    ->label('Type de congé')
                    ->relationship('typeConge', 'nom')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('annee')
                    ->label('Année')
                    ->options(function () {
                        $years = [];
                        $currentYear = (int) date('Y');
                        for ($i = $currentYear - 5; $i <= $currentYear + 1; $i++) {
                            $years[$i] = (string) $i;
                        }
                        return $years;
                    }),
                Tables\Filters\Filter::make('solde_epuise')
                    ->label('Solde épuisé')
                    ->query(fn (Builder $query): Builder => $query->where('solde_restant', '<=', 0)),
                Tables\Filters\Filter::make('solde_faible')
                    ->label('Solde faible')
                    ->query(fn (Builder $query): Builder => $query->where('solde_restant', '>', 0)->where('solde_restant', '<=', 5)),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('ajouter')
                    ->label('Ajouter')
                    ->icon('heroicon-o-plus')
                    ->color('success')
                    ->form([
                        Forms\Components\TextInput::make('jours')
                            ->label('Nombre de jours')
                            ->required()
                            ->numeric()
                            ->step(0.5)
                            ->minValue(0.5),
                        Forms\Components\Textarea::make('motif')
                            ->label('Motif')
                            ->required()
                            ->maxLength(255),
                    ])
                    ->action(function (array $data, ?SoldeConge $record) {
                        if ($record) {
                            $record->ajouterSolde($data['jours'], $data['motif']);
                        }
                    }),
                Tables\Actions\Action::make('deduire')
                    ->label('Déduire')
                    ->icon('heroicon-o-minus')
                    ->color('danger')
                    ->form([
                        Forms\Components\TextInput::make('jours')
                            ->label('Nombre de jours')
                            ->required()
                            ->numeric()
                            ->step(0.5)
                            ->minValue(0.5),
                        Forms\Components\Textarea::make('motif')
                            ->label('Motif')
                            ->required()
                            ->maxLength(255),
                    ])
                    ->action(function (array $data, ?SoldeConge $record) {
                        if ($record) {
                            $record->deduireSolde($data['jours'], $data['motif']);
                        }
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('reinitialiserSoldes')
                        ->label('Réinitialiser les soldes')
                        ->icon('heroicon-o-arrow-path')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->form([
                            Forms\Components\TextInput::make('solde_initial')
                                ->label('Nouveau solde initial')
                                ->required()
                                ->numeric()
                                ->step(0.5)
                                ->default(0.00),
                            Forms\Components\Textarea::make('commentaire')
                                ->label('Commentaire')
                                ->required()
                                ->maxLength(255),
                        ])
                        ->action(function (array $records, array $data) {
                            foreach ($records as $record) {
                                $record->update([
                                    'solde_initial' => $data['solde_initial'],
                                    'solde_acquis' => 0,
                                    'solde_pris' => 0,
                                    'solde_restant' => $data['solde_initial'],
                                    'date_derniere_maj' => now(),
                                    'commentaire' => $data['commentaire'],
                                ]);
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
            'index' => Pages\ListSoldeConges::route('/'),
            'create' => Pages\CreateSoldeConge::route('/create'),
            'view' => Pages\ViewSoldeConge::route('/{record}'),
            'edit' => Pages\EditSoldeConge::route('/{record}/edit'),
        ];
    }
    
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        
        $user = auth()->user();
        
        // Si l'utilisateur n'est pas SuperAdmin ou Support, filtrer les soldes
        if (!$user->isSuperAdmin() && !$user->isSupport()) {
            // Si l'utilisateur est manager ou admin, montrer les soldes des employés de son entreprise
            if ($user->isManager() || $user->isAdmin()) {
                // Logique pour récupérer les employés sous la responsabilité du manager
                $query->whereHas('employeur', function ($query) use ($user) {
                    $query->where('entreprise_id', $user->entreprise_id);
                });
            } else {
                // Utilisateur standard, ne voir que ses propres soldes
                $query->whereHas('employeur', function ($query) use ($user) {
                    $query->where('user_id', $user->id);
                });
            }
        }
        
        return $query;
    }
}
