<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FacturationResource\Pages;
use App\Filament\Resources\FacturationResource\RelationManagers;
use App\Models\Facturation;
use App\Models\Entreprise;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Notifications\Notification;
use Filament\Support\Enums\FontWeight;
use Illuminate\Support\Facades\Auth;
use App\Policies\FacturationPolicy;

class FacturationResource extends Resource
{
    protected static ?string $model = Facturation::class;
    protected static ?string $navigationIcon = 'heroicon-o-receipt-percent';
    protected static ?string $navigationGroup = 'Abonnements';
    protected static ?int $navigationSort = 2;
    protected static ?string $recordTitleAttribute = 'numero_facture';
    protected static string $policy = FacturationPolicy::class;

    public static function getNavigationLabel(): string
    {
        return __('Facturations');
    }

    public static function canAccess(): bool
    {
        return auth()->user()->isSuperAdmin() || auth()->user()->isSupport();
    }

    public static function getNavigationBadge(): ?string
    {
        // Afficher le nombre de facturations impayées
        $query = Facturation::query()->impaye();
        
        // Si l'utilisateur n'est pas un super admin, filtrer par entreprise
        if (!auth()->user()->isSuperAdmin()) {
            $query->where('entreprise_id', auth()->user()->entreprise_id);
        }
        
        $count = $query->count();
        return $count > 0 ? $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informations de facturation')
                    ->schema([
                        Forms\Components\Select::make('entreprise_id')
                            ->relationship('entreprise', 'nom')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->disabled(fn ($livewire) => !auth()->user()->isSuperAdmin()),
                        Forms\Components\Select::make('abonnement_id')
                            ->relationship('abonnement', 'id', function (Builder $query) {
                                // Si l'utilisateur n'est pas un super admin, filtrer par entreprise
                                if (!auth()->user()->isSuperAdmin()) {
                                    $query->where('entreprise_id', auth()->user()->entreprise_id);
                                }
                                return $query;
                            })
                            ->searchable()
                            ->preload()
                            ->reactive()
                            ->required()
                            ->afterStateUpdated(function ($state, callable $set, $get) {
                                if ($state) {
                                    $abonnement = \App\Models\Abonnement::find($state);
                                    if ($abonnement) {
                                        $set('entreprise_id', $abonnement->entreprise_id);
                                        $set('montant_ht', $abonnement->montant);
                                    }
                                }
                            }),
                        Forms\Components\TextInput::make('numero_facture')
                            ->required()
                            ->maxLength(255)
                            ->default(function () {
                                // Générer un numéro de facture unique
                                $prefix = 'FACT';
                                $timestamp = now()->format('YmdHis');
                                $random = strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 4));
                                return "{$prefix}-{$timestamp}-{$random}";
                            }),
                    ])->columns(2),
                Forms\Components\Section::make('Dates')
                    ->schema([
                        Forms\Components\DatePicker::make('date_facturation')
                            ->required()
                            ->default(now()),
                        Forms\Components\DatePicker::make('date_echeance')
                            ->required()
                            ->default(fn () => now()->addDays(30)),
                    ])->columns(2),
                Forms\Components\Section::make('Montants')
                    ->schema([
                        Forms\Components\TextInput::make('montant_ht')
                            ->required()
                            ->numeric()
                            ->prefix('XOF')
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set, $get) {
                                $tauxTva = $get('taux_tva') ?: 0;
                                $montantTva = $state * ($tauxTva / 100);
                                $set('montant_tva', $montantTva);
                                $set('montant_ttc', $state + $montantTva);
                            }),
                        Forms\Components\TextInput::make('taux_tva')
                            ->required()
                            ->numeric()
                            ->suffix('%')
                            ->default(18)
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set, $get) {
                                $montantHt = $get('montant_ht') ?: 0;
                                $montantTva = $montantHt * ($state / 100);
                                $set('montant_tva', $montantTva);
                                $set('montant_ttc', $montantHt + $montantTva);
                            }),
                        Forms\Components\TextInput::make('montant_tva')
                            ->numeric()
                            ->prefix('XOF')
                            ->disabled()
                            ->dehydrated(),
                        Forms\Components\TextInput::make('montant_ttc')
                            ->numeric()
                            ->prefix('XOF')
                            ->disabled()
                            ->dehydrated(),
                        Forms\Components\TextInput::make('devise')
                            ->required()
                            ->default('XOF')
                            ->maxLength(10),
                    ])->columns(2),
                Forms\Components\Section::make('Paiement')
                    ->schema([
                        Forms\Components\Select::make('statut_paiement')
                            ->options([
                                'impaye' => 'Impayé',
                                'paye' => 'Payé',
                                'partiel' => 'Partiellement payé',
                                'annule' => 'Annulé',
                            ])
                            ->required()
                            ->default('impaye'),
                        Forms\Components\Select::make('mode_paiement')
                            ->options([
                                'carte' => 'Carte bancaire',
                                'virement' => 'Virement bancaire',
                                'mobile_money' => 'Mobile Money',
                                'especes' => 'Espèces',
                                'cheque' => 'Chèque',
                            ]),
                        Forms\Components\TextInput::make('reference_paiement')
                            ->maxLength(255),
                        Forms\Components\Textarea::make('notes')
                            ->maxLength(1000)
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('numero_facture')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::Bold),
                Tables\Columns\TextColumn::make('entreprise.nom')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('date_facturation')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('date_echeance')
                    ->date()
                    ->sortable()
                    ->color(fn (Facturation $record) => $record->isEchu() && !$record->isPaye() ? 'danger' : null),
                Tables\Columns\TextColumn::make('montant_ttc')
                    ->money('XOF')
                    ->sortable(),
                Tables\Columns\TextColumn::make('statut_paiement')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'paye' => 'success',
                        'impaye' => 'danger',
                        'partiel' => 'warning',
                        'annule' => 'gray',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('mode_paiement')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('reference_paiement')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
                Tables\Filters\SelectFilter::make('statut_paiement')
                    ->options([
                        'impaye' => 'Impayé',
                        'paye' => 'Payé',
                        'partiel' => 'Partiellement payé',
                        'annule' => 'Annulé',
                    ]),
                Tables\Filters\SelectFilter::make('entreprise_id')
                    ->relationship('entreprise', 'nom')
                    ->searchable()
                    ->preload()
                    ->label('Entreprise')
                    ->visible(fn () => auth()->user()->isSuperAdmin()),
                Tables\Filters\Filter::make('date_echeance')
                    ->form([
                        Forms\Components\DatePicker::make('date_echeance_from')
                            ->label('Échéance depuis'),
                        Forms\Components\DatePicker::make('date_echeance_to')
                            ->label('Échéance jusqu\'à'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['date_echeance_from'],
                                fn (Builder $query, $date): Builder => $query->where('date_echeance', '>=', $date),
                            )
                            ->when(
                                $data['date_echeance_to'],
                                fn (Builder $query, $date): Builder => $query->where('date_echeance', '<=', $date),
                            );
                    }),
                Tables\Filters\Filter::make('echu')
                    ->label('Factures échues')
                    ->query(fn (Builder $query): Builder => $query->where('date_echeance', '<', now())->where('statut_paiement', 'impaye')),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('marquer_paye')
                    ->label('Marquer comme payé')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->authorize('marquerPaye')
                    ->visible(fn (Facturation $record) => $record->statut_paiement !== 'paye' && auth()->user()->isSuperAdmin())
                    ->action(function (Facturation $record) {
                        $record->update([
                            'statut_paiement' => 'paye',
                        ]);
                        
                        Notification::make()
                            ->title('Facturation marquée comme payée')
                            ->success()
                            ->send();
                    }),
                Tables\Actions\Action::make('generer_paiement')
                    ->label('Générer un paiement')
                    ->icon('heroicon-o-currency-dollar')
                    ->color('primary')
                    ->requiresConfirmation()
                    ->authorize('genererPaiement')
                    ->visible(fn (Facturation $record) => $record->statut_paiement !== 'paye' && auth()->user()->isSuperAdmin())
                    ->form([
                        Forms\Components\Select::make('methode')
                            ->options([
                                'card' => 'Carte bancaire',
                                'mobile_money' => 'Mobile Money',
                                'virement' => 'Virement bancaire',
                                'especes' => 'Espèces',
                                'cheque' => 'Chèque',
                                'autre' => 'Autre',
                            ])
                            ->required()
                            ->default('card'),
                        Forms\Components\Select::make('passerelle')
                            ->options([
                                'paystack' => 'Paystack',
                                'stripe' => 'Stripe',
                                'manuel' => 'Manuel',
                                'autre' => 'Autre',
                            ])
                            ->required()
                            ->default('manuel'),
                        Forms\Components\TextInput::make('reference_externe')
                            ->label('Référence externe')
                            ->maxLength(255),
                        Forms\Components\Textarea::make('commentaire')
                            ->maxLength(1000),
                    ])
                    ->action(function (Facturation $record, array $data) {
                        // Créer un paiement
                        $paiement = \App\Models\Paiement::create([
                            'facturation_id' => $record->id,
                            'abonnement_id' => $record->abonnement_id,
                            'entreprise_id' => $record->entreprise_id,
                            'initiateur_id' => auth()->id(),
                            'reference' => \App\Models\Paiement::genererReference(),
                            'reference_externe' => $data['reference_externe'] ?? null,
                            'montant' => $record->montant_ttc,
                            'devise' => $record->devise,
                            'methode' => $data['methode'],
                            'passerelle' => $data['passerelle'],
                            'statut' => $data['passerelle'] === 'manuel' ? 'en_attente' : 'complete',
                            'date_paiement' => now(),
                            'commentaire' => $data['commentaire'] ?? null,
                        ]);
                        
                        // Si le paiement est manuel, il reste en attente
                        if ($data['passerelle'] === 'manuel') {
                            Notification::make()
                                ->title('Paiement créé')
                                ->body('Le paiement a été créé et est en attente de validation.')
                                ->success()
                                ->send();
                        } else {
                            // Sinon, on marque directement la facturation comme payée
                            $record->update([
                                'statut_paiement' => 'paye',
                                'reference_paiement' => $paiement->reference,
                            ]);
                            
                            Notification::make()
                                ->title('Paiement effectué')
                                ->body('Le paiement a été effectué et la facturation a été marquée comme payée.')
                                ->success()
                                ->send();
                        }
                    }),
                Tables\Actions\DeleteAction::make()
                    ->visible(fn () => auth()->user()->isSuperAdmin()),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->before(function (\Illuminate\Database\Eloquent\Collection $records) {
                            $records->each(function ($record) {
                                if (!auth()->user()->can('delete', $record)) {
                                    $records->forget($record->getKey());
                                }
                            });
                        }),
                    
                ]),
            ])
            ->defaultSort('date_facturation', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\PaiementsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFacturations::route('/'),
            'create' => Pages\CreateFacturation::route('/create'),
            'view' => Pages\ViewFacturation::route('/{record}'),
            'edit' => Pages\EditFacturation::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        
        // Si l'utilisateur n'est pas un super admin, filtrer par entreprise
        if (!auth()->user()->isSuperAdmin()) {
            $query->where('entreprise_id', auth()->user()->entreprise_id);
        }
        
        return $query;
    }
}
