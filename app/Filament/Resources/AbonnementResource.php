<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AbonnementResource\Pages;
use App\Filament\Resources\AbonnementResource\RelationManagers;
use App\Models\Abonnement;
use App\Models\Entreprise;
use App\Models\PlanAbonnement;
use App\Services\AbonnementService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Policies\AbonnementPolicy;

class AbonnementResource extends Resource
{
    protected static ?string $model = Abonnement::class;
    protected static ?string $navigationIcon = 'heroicon-o-credit-card';
    protected static ?string $navigationGroup = 'Abonnements';
    protected static ?int $navigationSort = 1;
    protected static ?string $recordTitleAttribute = 'id';
    protected static string $policy = AbonnementPolicy::class;

    public static function getNavigationLabel(): string
    {
        return __('Gestion des abonnements');
    }

    public static function getNavigationBadge(): ?string
    {
        // Afficher le nombre d'abonnements qui expirent dans les 7 prochains jours
        $abonnementService = app(AbonnementService::class);
        $count = $abonnementService->getAbonnementsExpirantBientot(7)->count();
        return $count > 0 ? $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informations de l\'abonnement')
                    ->schema([
                        Forms\Components\Select::make('entreprise_id')
                            ->relationship('entreprise', 'nom')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->disabled(fn ($livewire) => !auth()->user()->isSuperAdmin()),
                        Forms\Components\Select::make('plan_abonnement_id')
                            ->relationship('planAbonnement', 'nom')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set) {
                                if ($state) {
                                    $plan = PlanAbonnement::find($state);
                                    if ($plan) {
                                        $set('montant', $plan->prix_mensuel);
                                    }
                                }
                            }),
                        Forms\Components\DateTimePicker::make('date_debut')
                            ->required()
                            ->default(now()),
                        Forms\Components\DateTimePicker::make('date_fin')
                            ->required()
                            ->default(fn () => now()->addDays(30)),
                        Forms\Components\Select::make('type_periode')
                            ->options([
                                'essai' => 'Période d\'essai',
                                'mensuel' => 'Mensuel',
                                'annuel' => 'Annuel',
                            ])
                            ->required()
                            ->default('mensuel')
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set, $get) {
                                $dateDebut = $get('date_debut') ? Carbon::parse($get('date_debut')) : now();
                                if ($state === 'mensuel') {
                                    $set('date_fin', $dateDebut->copy()->addDays(30));
                                } elseif ($state === 'annuel') {
                                    $set('date_fin', $dateDebut->copy()->addDays(365));
                                } elseif ($state === 'essai') {
                                    $set('date_fin', $dateDebut->copy()->addDays(14));
                                }
                                
                                // Mettre à jour le montant en fonction du type de période
                                $planId = $get('plan_abonnement_id');
                                if ($planId) {
                                    $plan = PlanAbonnement::find($planId);
                                    if ($plan) {
                                        $montant = $state === 'mensuel' ? $plan->prix_mensuel : $plan->prix_annuel;
                                        $set('montant', $montant);
                                    }
                                }
                            }),
                    ])->columns(2),
                Forms\Components\Section::make('Détails financiers')
                    ->schema([
                        Forms\Components\TextInput::make('montant')
                            ->numeric()
                            ->required()
                            ->prefix('XOF'),
                        Forms\Components\TextInput::make('reduction_code_promo')
                            ->numeric()
                            ->prefix('XOF')
                            ->default(0),
                        Forms\Components\Select::make('code_promo_id')
                            ->relationship('codePromo', 'code')
                            ->searchable()
                            ->preload(),
                        Forms\Components\Select::make('mode_paiement')
                            ->options([
                                'carte' => 'Carte bancaire',
                                'virement' => 'Virement bancaire',
                                'mobile_money' => 'Mobile Money',
                                'especes' => 'Espèces',
                                'cheque' => 'Chèque',
                            ])
                            ->required()
                            ->default('carte'),
                        Forms\Components\TextInput::make('reference_paiement')
                            ->maxLength(255),
                        Forms\Components\Select::make('methode_paiement')
                            ->options([
                                'stripe' => 'Stripe',
                                'paypal' => 'PayPal',
                                'orange_money' => 'Orange Money',
                                'mtn_mobile_money' => 'MTN Mobile Money',
                                'wave' => 'Wave',
                                'autre' => 'Autre',
                            ]),
                        Forms\Components\TextInput::make('reference_client')
                            ->maxLength(255),
                    ])->columns(2),
                Forms\Components\Section::make('Paramètres de l\'abonnement')
                    ->schema([
                        Forms\Components\Select::make('statut')
                            ->options([
                                'actif' => 'Actif',
                                'inactif' => 'Inactif',
                                'suspendu' => 'Suspendu',
                                'expire' => 'Expiré',
                                'resilie' => 'Résilié',
                            ])
                            ->required()
                            ->default('actif'),
                        Forms\Components\Toggle::make('renouvellement_automatique')
                            ->default(false)
                            ->inline(false)
                            ->onColor('success')
                            ->offColor('danger'),
                        Forms\Components\Toggle::make('facture_automatique')
                            ->default(true)
                            ->inline(false)
                            ->onColor('success')
                            ->offColor('danger'),
                        Forms\Components\Select::make('periode_facturation')
                            ->options([
                                'mensuel' => 'Mensuel',
                                'trimestriel' => 'Trimestriel',
                                'semestriel' => 'Semestriel',
                                'annuel' => 'Annuel',
                            ])
                            ->default('mensuel'),
                        Forms\Components\TextInput::make('nombre_personnels')
                            ->numeric()
                            ->minValue(1)
                            ->default(1),
                    ])->columns(2),
                Forms\Components\Section::make('Notes')
                    ->schema([
                        Forms\Components\Textarea::make('notes')
                            ->maxLength(1000)
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('notes_facturation')
                            ->maxLength(1000)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\FacturationsRelationManager::class,
            RelationManagers\PaiementsRelationManager::class,
        ];
    }

    public static function table(Table $table): Table
    {   
        $user = auth()->user();
        $isSuperAdminOrSupport = $user->isSuperAdmin() || $user->isSupport();
        
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('entreprise.nom')
                    ->searchable()
                    ->sortable()
                    ->visible($isSuperAdminOrSupport)
                    ->toggleable(),
                Tables\Columns\TextColumn::make('planAbonnement.nom')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('date_debut')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('date_fin')
                    ->dateTime()
                    ->sortable()
                    ->color(fn (Abonnement $record): string => $record->date_fin < now() ? 'danger' : 'success'),
                Tables\Columns\TextColumn::make('type_periode')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'essai' => 'info',
                        'mensuel' => 'warning',
                        'annuel' => 'success',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('montant')
                    ->money('XOF')
                    ->sortable(),
                Tables\Columns\TextColumn::make('statut')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'actif' => 'success',
                        'inactif' => 'danger',
                        'suspendu' => 'warning',
                        'expire' => 'danger',
                        'resilie' => 'gray',
                        default => 'gray',
                    }),
                Tables\Columns\IconColumn::make('renouvellement_automatique')
                    ->boolean()
                    ->label('Auto-renouvellement'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('statut')
                    ->options([
                        'actif' => 'Actif',
                        'inactif' => 'Inactif',
                        'suspendu' => 'Suspendu',
                        'expire' => 'Expiré',
                        'resilie' => 'Résilié',
                    ]),
                Tables\Filters\SelectFilter::make('type_periode')
                    ->options([
                        'essai' => 'Période d\'essai',
                        'mensuel' => 'Mensuel',
                        'annuel' => 'Annuel',
                    ]),
                Tables\Filters\Filter::make('date_fin')
                    ->form([
                        Forms\Components\DatePicker::make('expire_avant')
                            ->label('Expire avant'),
                        Forms\Components\DatePicker::make('expire_apres')
                            ->label('Expire après'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['expire_avant'],
                                fn (Builder $query, $date): Builder => $query->where('date_fin', '<=', $date),
                            )
                            ->when(
                                $data['expire_apres'],
                                fn (Builder $query, $date): Builder => $query->where('date_fin', '>=', $date),
                            );
                    }),
                Tables\Filters\TernaryFilter::make('renouvellement_automatique')
                    ->label('Auto-renouvellement'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('renouveler')
                    ->label('Renouveler')
                    ->icon('heroicon-o-arrow-path')
                    ->color('success')
                    ->requiresConfirmation()
                    ->authorize('renouveler')
                    ->visible(fn (Abonnement $record) => $record->statut === 'actif' && auth()->user()->isSuperAdmin())
                    ->form([
                        Forms\Components\Select::make('type_periode')
                            ->options([
                                'mensuel' => 'Mensuel',
                                'annuel' => 'Annuel',
                            ])
                            ->required()
                            ->default('mensuel'),
                    ])
                    ->action(function (Abonnement $record, array $data, AbonnementService $abonnementService) {
                        $abonnementService->renouvelerAbonnement($record, [
                            'type_periode' => $data['type_periode'],
                            'date_debut' => now(),
                        ]);
                    }),
                Tables\Actions\Action::make('activer')
                    ->label('Activer')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->authorize('activer')
                    ->visible(fn (Abonnement $record) => $record->statut !== 'actif')
                    ->action(function (Abonnement $record, AbonnementService $abonnementService) {
                        $abonnementService->changerStatutAbonnement($record, true);
                    }),
                Tables\Actions\Action::make('desactiver')
                    ->label('Désactiver')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->authorize('desactiver')
                    ->visible(fn (Abonnement $record) => $record->statut === 'actif')
                    ->action(function (Abonnement $record, AbonnementService $abonnementService) {
                        $abonnementService->changerStatutAbonnement($record, false);
                    }),
                Tables\Actions\Action::make('changer_plan')
                    ->label('Changer de plan')
                    ->icon('heroicon-o-arrow-up-tray')
                    ->color('warning')
                    ->authorize('changerPlan')
                    ->form([
                        Forms\Components\Select::make('plan_abonnement_id')
                            ->label('Nouveau plan')
                            ->options(PlanAbonnement::all()->pluck('nom', 'id'))
                            ->required(),
                        Forms\Components\Select::make('type_periode')
                            ->options([
                                'mensuel' => 'Mensuel',
                                'annuel' => 'Annuel',
                            ])
                            ->required()
                            ->default('mensuel'),
                    ])
                    ->action(function (Abonnement $record, array $data, AbonnementService $abonnementService) {
                        $nouveauPlan = PlanAbonnement::find($data['plan_abonnement_id']);
                        $abonnementService->changerPlanAbonnement($record, $nouveauPlan, [
                            'type_periode' => $data['type_periode'],
                        ]);
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()->visible(fn ($livewire) => auth()->user()->isSuperAdmin()),
                    Tables\Actions\BulkAction::make('activer_multiple')
                        ->label('Activer sélectionnés')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->visible(fn ($livewire) => auth()->user()->isSuperAdmin())
                        ->requiresConfirmation()
                        ->action(function (AbonnementService $abonnementService, \Illuminate\Database\Eloquent\Collection $records) {
                            foreach ($records as $record) {
                                if (auth()->user()->can('activer', $record)) {
                                    $abonnementService->changerStatutAbonnement($record, true);
                                }
                            }
                        }),
                    Tables\Actions\BulkAction::make('desactiver_multiple')
                        ->label('Désactiver sélectionnés')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->visible(fn ($livewire) => auth()->user()->isSuperAdmin())
                        ->requiresConfirmation()
                        ->action(function (AbonnementService $abonnementService, \Illuminate\Database\Eloquent\Collection $records) {
                            foreach ($records as $record) {
                                if (auth()->user()->can('desactiver', $record)) {
                                    $abonnementService->changerStatutAbonnement($record, false);
                                }
                            }
                        }),
                ]),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        
        // Si l'utilisateur n'est pas un super admin, filtrer pour ne montrer que les abonnements de son entreprise
        if (!auth()->user()->isSuperAdmin()) {
            $entrepriseId = auth()->user()->entreprise_id;
            $query->where('entreprise_id', $entrepriseId);
        }
        
        return $query;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAbonnements::route('/'),
            'create' => Pages\CreateAbonnement::route('/create'),
            'view' => Pages\ViewAbonnement::route('/{record}'),
            'edit' => Pages\EditAbonnement::route('/{record}/edit'),
        ];
    }
}
