<?php

namespace App\Filament\Resources\FacturationResource\Pages;

use App\Filament\Resources\FacturationResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditFacturation extends EditRecord
{
    protected static string $resource = FacturationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make()
                ->visible(fn () => auth()->user()->isSuperAdmin()),
            Actions\Action::make('marquer_paye')
                ->label('Marquer comme payé')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->visible(fn () => $this->record->statut_paiement !== 'paye')
                ->action(function () {
                    $this->record->update([
                        'statut_paiement' => 'paye',
                    ]);
                    
                    Notification::make()
                        ->title('Facturation marquée comme payée')
                        ->success()
                        ->send();
                        
                    $this->redirect($this->getResource()::getUrl('view', ['record' => $this->record]));
                }),
            Actions\Action::make('generer_paiement')
                ->label('Générer un paiement')
                ->icon('heroicon-o-currency-dollar')
                ->color('primary')
                ->requiresConfirmation()
                ->visible(fn () => $this->record->statut_paiement !== 'paye')
                ->form([
                    \Filament\Forms\Components\Select::make('methode')
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
                    \Filament\Forms\Components\Select::make('passerelle')
                        ->options([
                            'paystack' => 'Paystack',
                            'stripe' => 'Stripe',
                            'manuel' => 'Manuel',
                            'autre' => 'Autre',
                        ])
                        ->required()
                        ->default('manuel'),
                    \Filament\Forms\Components\TextInput::make('reference_externe')
                        ->label('Référence externe')
                        ->maxLength(255),
                    \Filament\Forms\Components\Textarea::make('commentaire')
                        ->maxLength(1000),
                ])
                ->action(function (array $data) {
                    // Créer un paiement
                    $paiement = \App\Models\Paiement::create([
                        'facturation_id' => $this->record->id,
                        'abonnement_id' => $this->record->abonnement_id,
                        'entreprise_id' => $this->record->entreprise_id,
                        'initiateur_id' => auth()->id(),
                        'reference' => \App\Models\Paiement::genererReference(),
                        'reference_externe' => $data['reference_externe'] ?? null,
                        'montant' => $this->record->montant_ttc,
                        'devise' => $this->record->devise,
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
                        $this->record->update([
                            'statut_paiement' => 'paye',
                            'reference_paiement' => $paiement->reference,
                        ]);
                        
                        Notification::make()
                            ->title('Paiement effectué')
                            ->body('Le paiement a été effectué et la facturation a été marquée comme payée.')
                            ->success()
                            ->send();
                    }
                    
                    $this->redirect($this->getResource()::getUrl('view', ['record' => $this->record]));
                }),
        ];
    }
    
    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Calculer automatiquement les montants TVA et TTC
        $data['montant_tva'] = $data['montant_ht'] * ($data['taux_tva'] / 100);
        $data['montant_ttc'] = $data['montant_ht'] + $data['montant_tva'];
        
        return $data;
    }
    
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->record]);
    }
    
    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Facturation mise à jour')
            ->body('La facturation a été mise à jour avec succès.');
    }
}
