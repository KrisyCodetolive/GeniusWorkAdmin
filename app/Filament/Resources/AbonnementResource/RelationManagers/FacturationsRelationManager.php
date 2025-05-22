<?php

namespace App\Filament\Resources\AbonnementResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Models\Facturation;
use Filament\Notifications\Notification;

class FacturationsRelationManager extends RelationManager
{
    protected static string $relationship = 'facturations';

    protected static ?string $recordTitleAttribute = 'numero_facture';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informations de facturation')
                    ->schema([
                        Forms\Components\TextInput::make('numero_facture')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                        Forms\Components\DatePicker::make('date_facturation')
                            ->required()
                            ->default(now()),
                        Forms\Components\DatePicker::make('date_echeance')
                            ->required()
                            ->default(fn () => now()->addDays(30)),
                        Forms\Components\Select::make('statut_paiement')
                            ->options([
                                'impaye' => 'Impayé',
                                'paye' => 'Payé',
                                'partiel' => 'Partiellement payé',
                                'annule' => 'Annulé',
                            ])
                            ->required()
                            ->default('impaye'),
                    ])->columns(2),
                Forms\Components\Section::make('Montants')
                    ->schema([
                        Forms\Components\TextInput::make('montant_ht')
                            ->required()
                            ->numeric()
                            ->prefix('XOF')
                            ->default(function () {
                                return $this->ownerRecord->montant ?? 0;
                            }),
                        Forms\Components\TextInput::make('taux_tva')
                            ->required()
                            ->numeric()
                            ->suffix('%')
                            ->default(18),
                        Forms\Components\TextInput::make('montant_tva')
                            ->numeric()
                            ->prefix('XOF')
                            ->disabled()
                            ->dehydrated()
                            ->default(function (callable $get) {
                                $montantHt = $get('montant_ht') ?: 0;
                                $tauxTva = $get('taux_tva') ?: 0;
                                return $montantHt * ($tauxTva / 100);
                            }),
                        Forms\Components\TextInput::make('montant_ttc')
                            ->numeric()
                            ->prefix('XOF')
                            ->disabled()
                            ->dehydrated()
                            ->default(function (callable $get) {
                                $montantHt = $get('montant_ht') ?: 0;
                                $montantTva = $get('montant_tva') ?: 0;
                                return $montantHt + $montantTva;
                            }),
                        Forms\Components\TextInput::make('devise')
                            ->required()
                            ->default('XOF')
                            ->maxLength(10),
                    ])->columns(2),
                Forms\Components\Section::make('Paiement')
                    ->schema([
                        Forms\Components\Select::make('mode_paiement')
                            ->options([
                                'carte' => 'Carte bancaire',
                                'virement' => 'Virement bancaire',
                                'mobile_money' => 'Mobile Money',
                                'especes' => 'Espèces',
                                'cheque' => 'Chèque',
                            ])
                            ->required(),
                        Forms\Components\TextInput::make('reference_paiement')
                            ->maxLength(255),
                        Forms\Components\Textarea::make('notes')
                            ->maxLength(1000)
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('numero_facture')
            ->columns([
                Tables\Columns\TextColumn::make('numero_facture')
                    ->searchable()
                    ->sortable(),
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
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
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
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->mutateFormDataUsing(function (array $data) {
                        // Calculer automatiquement les montants TVA et TTC
                        $data['montant_tva'] = $data['montant_ht'] * ($data['taux_tva'] / 100);
                        $data['montant_ttc'] = $data['montant_ht'] + $data['montant_tva'];
                        
                        // Ajouter l'ID de l'entreprise
                        $data['entreprise_id'] = $this->ownerRecord->entreprise_id;
                        
                        return $data;
                    })
                    ->after(function (Facturation $record) {
                        Notification::make()
                            ->title('Facturation créée')
                            ->body('La facturation a été créée avec succès.')
                            ->success()
                            ->send();
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()
                    ->mutateFormDataUsing(function (array $data) {
                        // Calculer automatiquement les montants TVA et TTC
                        $data['montant_tva'] = $data['montant_ht'] * ($data['taux_tva'] / 100);
                        $data['montant_ttc'] = $data['montant_ht'] + $data['montant_tva'];
                        
                        return $data;
                    }),
                Tables\Actions\Action::make('marquer_paye')
                    ->label('Marquer comme payé')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
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
                        ->visible(fn () => auth()->user()->isSuperAdmin()),
                    Tables\Actions\BulkAction::make('marquer_payes')
                        ->label('Marquer comme payés')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->visible(fn () => auth()->user()->isSuperAdmin())
                        ->action(function (\Illuminate\Database\Eloquent\Collection $records) {
                            $records->each(function (Facturation $record) {
                                $record->update([
                                    'statut_paiement' => 'paye',
                                ]);
                            });
                            
                            Notification::make()
                                ->title('Facturations marquées comme payées')
                                ->success()
                                ->send();
                        }),
                ]),
            ])
            ->defaultSort('date_facturation', 'desc');
    }
}
