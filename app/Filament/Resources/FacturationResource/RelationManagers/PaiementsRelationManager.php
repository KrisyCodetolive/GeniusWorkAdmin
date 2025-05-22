<?php

namespace App\Filament\Resources\FacturationResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Models\Paiement;
use Filament\Notifications\Notification;

class PaiementsRelationManager extends RelationManager
{
    protected static string $relationship = 'paiements';

    protected static ?string $recordTitleAttribute = 'reference';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informations de paiement')
                    ->schema([
                        Forms\Components\TextInput::make('reference')
                            ->required()
                            ->maxLength(255)
                            ->default(fn () => Paiement::genererReference())
                            ->disabled(),
                        Forms\Components\TextInput::make('reference_externe')
                            ->label('Référence externe')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('montant')
                            ->required()
                            ->numeric()
                            ->prefix('XOF')
                            ->default(function () {
                                return $this->ownerRecord->montant_ttc ?? 0;
                            }),
                        Forms\Components\TextInput::make('devise')
                            ->required()
                            ->default('XOF')
                            ->maxLength(10),
                    ])->columns(2),
                Forms\Components\Section::make('Méthode de paiement')
                    ->schema([
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
                    ])->columns(2),
                Forms\Components\Section::make('Commentaires')
                    ->schema([
                        Forms\Components\Textarea::make('commentaire')
                            ->maxLength(1000)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('reference')
            ->columns([
                Tables\Columns\TextColumn::make('reference')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
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
                    ->searchable(),
                Tables\Columns\TextColumn::make('passerelle')
                    ->searchable(),
                Tables\Columns\TextColumn::make('initiateur.name')
                    ->label('Initiateur')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('validateur.name')
                    ->label('Validateur')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->mutateFormDataUsing(function (array $data) {
                        // Ajouter l'ID de l'entreprise, de l'abonnement et de l'initiateur
                        $data['entreprise_id'] = $this->ownerRecord->entreprise_id;
                        $data['abonnement_id'] = $this->ownerRecord->abonnement_id;
                        $data['initiateur_id'] = auth()->id();
                        
                        return $data;
                    })
                    ->after(function (Paiement $record) {
                        // Si le paiement est complété, mettre à jour le statut de la facturation
                        if ($record->statut === 'complete') {
                            $this->ownerRecord->update([
                                'statut_paiement' => 'paye',
                                'reference_paiement' => $record->reference,
                            ]);
                            
                            // Mettre à jour le statut de l'abonnement si nécessaire
                            if ($record->abonnement && $record->abonnement->statut !== 'actif') {
                                $record->abonnement->update([
                                    'statut' => 'actif',
                                ]);
                            }
                        }
                        
                        Notification::make()
                            ->title('Paiement créé')
                            ->body('Le paiement a été créé avec succès.')
                            ->success()
                            ->send();
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()
                    ->visible(fn (Paiement $record) => $record->statut !== 'complete' && auth()->user()->isSuperAdmin()),
                Tables\Actions\Action::make('valider')
                    ->label('Valider le paiement')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (Paiement $record) => $record->necessiteValidation() && auth()->user()->isSuperAdmin())
                    ->form([
                        Forms\Components\Textarea::make('commentaire')
                            ->label('Commentaire de validation')
                            ->maxLength(1000),
                    ])
                    ->action(function (Paiement $record, array $data) {
                        $record->valider(auth()->id(), $data['commentaire'] ?? null);
                        
                        // Mettre à jour la facturation
                        $this->ownerRecord->update([
                            'statut_paiement' => 'paye',
                            'reference_paiement' => $record->reference,
                        ]);
                        
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
                    ->visible(fn () => auth()->user()->isSuperAdmin()),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn () => auth()->user()->isSuperAdmin()),
                ]),
            ])
            ->defaultSort('date_paiement', 'desc');
    }
}
