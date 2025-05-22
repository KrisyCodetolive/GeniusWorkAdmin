<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaiementResource\Pages;
use App\Filament\Resources\PaiementResource\RelationManagers;
use App\Models\Paiement;
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
use App\Policies\PaiementPolicy;

class PaiementResource extends Resource
{
    protected static ?string $model = Paiement::class;
    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationGroup = 'Abonnements';
    protected static ?int $navigationSort = 3;
    protected static ?string $recordTitleAttribute = 'reference';
    protected static string $policy = PaiementPolicy::class;

    public static function getNavigationLabel(): string
    {
        return __('Paiements');
    }

    public static function canAccess(): bool
    {
        return auth()->user()->isSuperAdmin() || auth()->user()->isSupport();
    }

    public static function getNavigationBadge(): ?string
    {
        // Afficher le nombre de paiements en attente de validation
        $query = Paiement::query()->where('statut', Paiement::STATUT_EN_ATTENTE);
        
        // Si l'utilisateur n'est pas un super admin, filtrer par entreprise
        if (!auth()->user()->isSuperAdmin()) {
            $query->where('entreprise_id', auth()->user()->entreprise_id);
        }
        
        $count = $query->count();
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
                Forms\Components\Section::make('Informations de paiement')
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
                            ->afterStateUpdated(function ($state, callable $set, $get) {
                                if ($state) {
                                    $abonnement = \App\Models\Abonnement::find($state);
                                    if ($abonnement) {
                                        $set('entreprise_id', $abonnement->entreprise_id);
                                        $set('montant', $abonnement->montant);
                                    }
                                }
                            }),
                        Forms\Components\Select::make('facturation_id')
                            ->relationship('facturation', 'numero_facture', function (Builder $query) {
                                // Si l'utilisateur n'est pas un super admin, filtrer par entreprise
                                if (!auth()->user()->isSuperAdmin()) {
                                    $query->where('entreprise_id', auth()->user()->entreprise_id);
                                }
                                return $query;
                            })
                            ->searchable()
                            ->preload()
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set, $get) {
                                if ($state) {
                                    $facturation = \App\Models\Facturation::find($state);
                                    if ($facturation) {
                                        $set('entreprise_id', $facturation->entreprise_id);
                                        $set('abonnement_id', $facturation->abonnement_id);
                                        $set('montant', $facturation->montant_ttc);
                                    }
                                }
                            }),
                        Forms\Components\TextInput::make('reference')
                            ->required()
                            ->maxLength(255)
                            ->default(fn () => Paiement::genererReference())
                            ->disabled(),
                        Forms\Components\TextInput::make('reference_externe')
                            ->label('Référence externe')
                            ->maxLength(255),
                    ])->columns(2),
                Forms\Components\Section::make('Montant et méthode')
                    ->schema([
                        Forms\Components\TextInput::make('montant')
                            ->required()
                            ->numeric()
                            ->prefix('XOF'),
                        Forms\Components\TextInput::make('devise')
                            ->required()
                            ->default('XOF')
                            ->maxLength(10),
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
                    ])->columns(2),
                Forms\Components\Section::make('Statut et dates')
                    ->schema([
                        Forms\Components\Select::make('statut')
                            ->options([
                                'en_attente' => 'En attente',
                                'en_traitement' => 'En traitement',
                                'complete' => 'Complété',
                                'echoue' => 'Échoué',
                                'rembourse' => 'Remboursé',
                                'annule' => 'Annulé',
                                'rejete' => 'Rejeté',
                            ])
                            ->required()
                            ->default('en_attente'),
                        Forms\Components\DateTimePicker::make('date_paiement')
                            ->required()
                            ->default(now()),
                        Forms\Components\DateTimePicker::make('date_validation')
                            ->disabled(),
                        Forms\Components\Select::make('validateur_id')
                            ->relationship('validateur', 'name')
                            ->searchable()
                            ->preload()
                            ->disabled(),
                    ])->columns(2),
                Forms\Components\Section::make('Commentaires')
                    ->schema([
                        Forms\Components\Textarea::make('commentaire')
                            ->maxLength(1000)
                            ->columnSpanFull(),
                        Forms\Components\KeyValue::make('meta_donnees')
                            ->label('Métadonnées')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('reference')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::Bold)
                    ->copyable(),
                Tables\Columns\TextColumn::make('entreprise.nom')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('date_paiement')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('montant')
                    ->money('XOF')
                    ->sortable(),
                Tables\Columns\TextColumn::make('statut')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'complete' => 'success',
                        'en_attente' => 'warning',
                        'en_traitement' => 'info',
                        'echoue' => 'danger',
                        'rembourse' => 'gray',
                        'annule' => 'gray',
                        'rejete' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('methode')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('passerelle')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('initiateur.name')
                    ->label('Initiateur')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('validateur.name')
                    ->label('Validateur')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('facturation.numero_facture')
                    ->label('Facture')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('statut')
                    ->options([
                        'en_attente' => 'En attente',
                        'en_traitement' => 'En traitement',
                        'complete' => 'Complété',
                        'echoue' => 'Échoué',
                        'rembourse' => 'Remboursé',
                        'annule' => 'Annulé',
                        'rejete' => 'Rejeté',
                    ]),
                Tables\Filters\SelectFilter::make('methode')
                    ->options([
                        'card' => 'Carte bancaire',
                        'mobile_money' => 'Mobile Money',
                        'virement' => 'Virement bancaire',
                        'especes' => 'Espèces',
                        'cheque' => 'Chèque',
                        'autre' => 'Autre',
                    ]),
                Tables\Filters\SelectFilter::make('passerelle')
                    ->options([
                        'paystack' => 'Paystack',
                        'stripe' => 'Stripe',
                        'manuel' => 'Manuel',
                        'autre' => 'Autre',
                    ]),
                Tables\Filters\SelectFilter::make('entreprise_id')
                    ->relationship('entreprise', 'nom')
                    ->searchable()
                    ->preload()
                    ->label('Entreprise')
                    ->visible(fn () => auth()->user()->isSuperAdmin()),
                Tables\Filters\Filter::make('date_paiement')
                    ->form([
                        Forms\Components\DatePicker::make('date_paiement_from')
                            ->label('Date depuis'),
                        Forms\Components\DatePicker::make('date_paiement_to')
                            ->label('Date jusqu\'à'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['date_paiement_from'],
                                fn (Builder $query, $date): Builder => $query->where('date_paiement', '>=', $date),
                            )
                            ->when(
                                $data['date_paiement_to'],
                                fn (Builder $query, $date): Builder => $query->where('date_paiement', '<=', $date),
                            );
                    }),
                Tables\Filters\Filter::make('necessiteValidation')
                    ->label('Nécessite validation')
                    ->query(fn (Builder $query): Builder => $query->where('passerelle', 'manuel')->where('statut', 'en_attente')),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()
                    ->visible(fn (Paiement $record) => $record->statut !== 'complete')
                    ->authorize('update'),
                Tables\Actions\Action::make('valider')
                    ->label('Valider le paiement')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (Paiement $record) => $record->necessiteValidation() && auth()->user()->isSuperAdmin())
                    ->authorize('valider')
                    ->form([
                        Forms\Components\Textarea::make('commentaire')
                            ->label('Commentaire de validation')
                            ->maxLength(1000),
                    ])
                    ->action(function (Paiement $record, array $data) {
                        $record->valider(auth()->id(), $data['commentaire'] ?? null);
                        
                        Notification::make()
                            ->title('Paiement validé')
                            ->body('Le paiement a été validé avec succès.')
                            ->success()
                            ->send();
                    }),
                Tables\Actions\Action::make('rejeter')
                    ->label('Rejeter le paiement')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (Paiement $record) => $record->necessiteValidation() && auth()->user()->isSuperAdmin())
                    ->authorize('rejeter')
                    ->form([
                        Forms\Components\Textarea::make('commentaire')
                            ->label('Motif du rejet')
                            ->required()
                            ->maxLength(1000),
                    ])
                    ->action(function (Paiement $record, array $data) {
                        $record->rejeter(auth()->id(), $data['commentaire']);
                        
                        Notification::make()
                            ->title('Paiement rejeté')
                            ->body('Le paiement a été rejeté.')
                            ->danger()
                            ->send();
                    }),
                Tables\Actions\DeleteAction::make()
                    ->authorize('delete'),
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
                    Tables\Actions\BulkAction::make('valider_paiements')
                        ->label('Valider les paiements')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function (\Illuminate\Database\Eloquent\Collection $records) {
                            $records->each(function (Paiement $record) {
                                if ($record->necessiteValidation() && auth()->user()->can('valider', $record)) {
                                    $record->valider(auth()->id());
                                }
                            });
                            
                            Notification::make()
                                ->title('Paiements validés')
                                ->body('Les paiements sélectionnés ont été validés avec succès.')
                                ->success()
                                ->send();
                        }),
                ]),
            ])
            ->defaultSort('date_paiement', 'desc');
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
            'index' => Pages\ListPaiements::route('/'),
            'create' => Pages\CreatePaiement::route('/create'),
            'view' => Pages\ViewPaiement::route('/{record}'),
            'edit' => Pages\EditPaiement::route('/{record}/edit'),
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
