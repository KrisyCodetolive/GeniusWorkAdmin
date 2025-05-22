<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FraisUsageResource\Pages;
use App\Models\FraisUsage;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Policies\FraisUsagePolicy;
use Illuminate\Support\Collection;

class FraisUsageResource extends Resource
{
    protected static ?string $model = FraisUsage::class;

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';

    protected static ?string $navigationLabel = 'Frais d\'usage';

    protected static ?string $navigationGroup = 'Abonnements';

    protected static ?int $navigationSort = 30;

    protected static ?string $recordTitleAttribute = 'description';

    protected static string $policy = FraisUsagePolicy::class;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informations générales')
                    ->schema([
                        Forms\Components\Select::make('entreprise_id')
                            ->relationship('entreprise', 'nom')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->columnSpan(['default' => 1, 'md' => 1]),

                        Forms\Components\Select::make('facturation_id')
                            ->relationship('facturation', 'numero_facture')
                            ->label('Facture associée')
                            ->searchable()
                            ->preload()
                            ->columnSpan(['default' => 1, 'md' => 1]),

                        Forms\Components\Select::make('type_frais')
                            ->label('Type de frais')
                            ->options([
                                'utilisateur_supplementaire' => 'Utilisateur supplémentaire',
                                'stockage_supplementaire' => 'Stockage supplémentaire',
                                'fonctionnalite_premium' => 'Fonctionnalité premium',
                                'support_technique' => 'Support technique',
                                'formation' => 'Formation',
                                'personnalisation' => 'Personnalisation',
                                'autre' => 'Autre',
                            ])
                            ->required()
                            ->columnSpan(['default' => 1, 'md' => 1]),

                        Forms\Components\TextInput::make('description')
                            ->label('Description')
                            ->required()
                            ->maxLength(255)
                            ->columnSpan(['default' => 1, 'md' => 1]),

                        Forms\Components\Select::make('statut')
                            ->label('Statut')
                            ->options([
                                'en_attente' => 'En attente',
                                'facture' => 'Facturé',
                                'paye' => 'Payé',
                                'annule' => 'Annulé',
                            ])
                            ->default('en_attente')
                            ->required()
                            ->columnSpan(['default' => 1, 'md' => 1]),

                        Forms\Components\Select::make('devise')
                            ->label('Devise')
                            ->options([
                                'EUR' => 'Euro (€)',
                                'USD' => 'Dollar US ($)',
                                'GBP' => 'Livre Sterling (£)',
                                'CHF' => 'Franc Suisse (CHF)',
                                'CAD' => 'Dollar Canadien (CAD)',
                            ])
                            ->default('EUR')
                            ->required()
                            ->columnSpan(['default' => 1, 'md' => 1]),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Période de facturation')
                    ->schema([
                        Forms\Components\DatePicker::make('periode_debut')
                            ->label('Début de période')
                            ->required()
                            ->columnSpan(['default' => 1, 'md' => 1]),

                        Forms\Components\DatePicker::make('periode_fin')
                            ->label('Fin de période')
                            ->required()
                            ->after('periode_debut')
                            ->columnSpan(['default' => 1, 'md' => 1]),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Montants')
                    ->schema([
                        Forms\Components\TextInput::make('quantite')
                            ->label('Quantité')
                            ->numeric()
                            ->default(1)
                            ->minValue(0)
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (callable $set, callable $get) {
                                $prixUnitaire = $get('prix_unitaire') ?: 0;
                                $quantite = $get('quantite') ?: 0;
                                $set('montant_total', $prixUnitaire * $quantite);
                            })
                            ->columnSpan(['default' => 1, 'md' => 1]),

                        Forms\Components\TextInput::make('prix_unitaire')
                            ->label('Prix unitaire')
                            ->numeric()
                            ->prefix(fn (callable $get) => $get('devise') === 'EUR' ? '€' : ($get('devise') === 'USD' ? '$' : ''))
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (callable $set, callable $get) {
                                $prixUnitaire = $get('prix_unitaire') ?: 0;
                                $quantite = $get('quantite') ?: 0;
                                $set('montant_total', $prixUnitaire * $quantite);
                            })
                            ->columnSpan(['default' => 1, 'md' => 1]),

                        Forms\Components\TextInput::make('montant_total')
                            ->label('Montant total')
                            ->numeric()
                            ->prefix(fn (callable $get) => $get('devise') === 'EUR' ? '€' : ($get('devise') === 'USD' ? '$' : ''))
                            ->disabled()
                            ->dehydrated()
                            ->columnSpan(['default' => 2, 'md' => 2]),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('entreprise.nom')
                    ->label('Entreprise')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('type_frais')
                    ->label('Type')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'utilisateur_supplementaire' => 'Utilisateur supp.',
                        'stockage_supplementaire' => 'Stockage supp.',
                        'fonctionnalite_premium' => 'Fonct. premium',
                        'support_technique' => 'Support tech.',
                        'formation' => 'Formation',
                        'personnalisation' => 'Personnalisation',
                        'autre' => 'Autre',
                        default => $state,
                    })
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('description')
                    ->label('Description')
                    ->searchable()
                    ->limit(30),
                Tables\Columns\TextColumn::make('quantite')
                    ->label('Qté')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('prix_unitaire')
                    ->label('Prix unit.')
                    ->money(fn (FraisUsage $record): string => $record->devise)
                    ->sortable(),
                Tables\Columns\TextColumn::make('montant_total')
                    ->label('Total')
                    ->money(fn (FraisUsage $record): string => $record->devise)
                    ->sortable(),
                Tables\Columns\TextColumn::make('periode_debut')
                    ->label('Début')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('periode_fin')
                    ->label('Fin')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('statut')
                    ->label('Statut')
                    ->colors([
                        'warning' => 'en_attente',
                        'success' => 'facture',
                        'primary' => 'paye',
                        'danger' => 'annule',
                    ]),
                Tables\Columns\TextColumn::make('facturation.numero_facture')
                    ->label('Facture')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Mis à jour le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
                Tables\Filters\SelectFilter::make('entreprise_id')
                    ->relationship('entreprise', 'nom')
                    ->label('Entreprise')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('type_frais')
                    ->label('Type de frais')
                    ->options([
                        'utilisateur_supplementaire' => 'Utilisateur supplémentaire',
                        'stockage_supplementaire' => 'Stockage supplémentaire',
                        'fonctionnalite_premium' => 'Fonctionnalité premium',
                        'support_technique' => 'Support technique',
                        'formation' => 'Formation',
                        'personnalisation' => 'Personnalisation',
                        'autre' => 'Autre',
                    ]),
                Tables\Filters\SelectFilter::make('statut')
                    ->label('Statut')
                    ->options([
                        'en_attente' => 'En attente',
                        'facture' => 'Facturé',
                        'paye' => 'Payé',
                        'annule' => 'Annulé',
                    ]),
                Tables\Filters\Filter::make('non_facture')
                    ->label('Non facturé')
                    ->query(fn (Builder $query): Builder => $query->whereNull('facturation_id'))
                    ->toggle(),
                Tables\Filters\Filter::make('periode')
                    ->form([
                        Forms\Components\DatePicker::make('debut')
                            ->label('Début'),
                        Forms\Components\DatePicker::make('fin')
                            ->label('Fin'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['debut'],
                                fn (Builder $query, $date): Builder => $query->where('periode_debut', '>=', $date),
                            )
                            ->when(
                                $data['fin'],
                                fn (Builder $query, $date): Builder => $query->where('periode_fin', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()
                    ->authorize('update'),
                Tables\Actions\Action::make('facturer')
                    ->label('Facturer')
                    ->icon('heroicon-o-document-text')
                    ->color('success')
                    ->visible(fn (FraisUsage $record): bool => $record->facturation_id === null)
                    ->authorize('facturer')
                    ->url(fn (FraisUsage $record): string => route('filament.admin.resources.facturations.create', [
                        'frais_ids' => [$record->id],
                    ])),
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
                    Tables\Actions\ForceDeleteBulkAction::make()
                        ->before(function (\Illuminate\Database\Eloquent\Collection $records) {
                            $records->each(function ($record) {
                                if (!auth()->user()->can('forceDelete', $record)) {
                                    $records->forget($record->getKey());
                                }
                            });
                        }),
                    Tables\Actions\RestoreBulkAction::make()
                        ->before(function (\Illuminate\Database\Eloquent\Collection $records) {
                            $records->each(function ($record) {
                                if (!auth()->user()->can('restore', $record)) {
                                    $records->forget($record->getKey());
                                }
                            });
                        }),
                    Tables\Actions\BulkAction::make('facturer')
                        ->label('Facturer la sélection')
                        ->icon('heroicon-o-document-text')
                        ->color('success')
                        ->action(function (Collection $records) {
                            $frais_ids = $records->filter(function ($record) {
                                return auth()->user()->can('facturer', $record);
                            })->pluck('id')->toArray();
                            
                            if (count($frais_ids) > 0) {
                                return redirect()->route('filament.admin.resources.facturations.create', [
                                    'frais_ids' => $frais_ids,
                                ]);
                            }
                        })
                        ->deselectRecordsAfterCompletion(),
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
            'index' => Pages\ListFraisUsages::route('/'),
            'create' => Pages\CreateFraisUsage::route('/create'),
            'view' => Pages\ViewFraisUsage::route('/{record}'),
            'edit' => Pages\EditFraisUsage::route('/{record}/edit'),
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
