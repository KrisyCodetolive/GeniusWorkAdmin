<?php

namespace App\Filament\Resources\DemandeDepenseResource\Pages;

use App\Filament\Resources\DemandeDepenseResource;
use App\Filament\Resources\DemandeDepenseResource\Actions\CircuitActions;
use App\Models\DemandeDepense;
use Filament\Actions;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewDemandeDepense extends ViewRecord
{
    protected static string $resource = DemandeDepenseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CircuitActions::soumettre(Actions\Action::class),
            CircuitActions::valider(Actions\Action::class),
            CircuitActions::decaisser(Actions\Action::class),
            CircuitActions::validerJustification(Actions\Action::class),
            CircuitActions::renvoyerJustification(Actions\Action::class),
            CircuitActions::renvoyer(Actions\Action::class),
            CircuitActions::rejeter(Actions\Action::class),
            CircuitActions::bonSortie(Actions\Action::class),
            Actions\EditAction::make(),
            CircuitActions::annuler(Actions\Action::class),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Demande')
                    ->schema([
                        Infolists\Components\TextEntry::make('reference')
                            ->label('Référence')
                            ->weight('bold'),
                        Infolists\Components\TextEntry::make('statut')
                            ->label('Statut')
                            ->badge()
                            ->formatStateUsing(fn (string $state) => DemandeDepense::STATUTS[$state] ?? $state)
                            ->color(fn (string $state) => DemandeDepense::COULEURS_STATUT[$state] ?? 'gray'),
                        Infolists\Components\TextEntry::make('montant')
                            ->label('Montant')
                            ->formatStateUsing(fn ($state, DemandeDepense $record) => number_format((float) $state, 0, ',', ' ').' '.$record->devise)
                            ->helperText(fn (DemandeDepense $record) => $record->montantEnLettres())
                            ->weight('bold'),
                        Infolists\Components\TextEntry::make('objet')
                            ->label('Objet'),
                        Infolists\Components\TextEntry::make('beneficiaire')
                            ->label('Bénéficiaire'),
                        Infolists\Components\TextEntry::make('demandeur')
                            ->label('Demandeur')
                            ->state(fn (DemandeDepense $record) => $record->nomDemandeur()),
                        Infolists\Components\TextEntry::make('categorie.nom')
                            ->label('Catégorie')
                            ->placeholder('—'),
                        Infolists\Components\TextEntry::make('departement.nom')
                            ->label('Département')
                            ->placeholder('—'),
                        Infolists\Components\TextEntry::make('date_besoin')
                            ->label('Date de besoin')
                            ->date('d/m/Y')
                            ->placeholder('—'),
                        Infolists\Components\TextEntry::make('creePar.name')
                            ->label('Créée par'),
                        Infolists\Components\TextEntry::make('created_at')
                            ->label('Créée le')
                            ->dateTime('d/m/Y à H:i'),
                        Infolists\Components\TextEntry::make('validation_ceo')
                            ->label('Validation CEO')
                            ->state(fn (DemandeDepense $record) => $record->necessiteValidationCeo()
                                ? 'Requise (montant au-dessus du seuil)'
                                : 'Non requise'),
                        Infolists\Components\TextEntry::make('signature_demandeur')
                            ->label('Décharge du demandeur')
                            ->state(fn (DemandeDepense $record) => ($v = $record->signatureDemandeur())
                                ? $v->user?->name.' — le '.$v->signe_le->format('d/m/Y à H:i')
                                : 'En attente de signature')
                            ->badge()
                            ->color(fn (DemandeDepense $record) => $record->signatureDemandeur() ? 'success' : 'warning')
                            ->url(fn (DemandeDepense $record) => ($v = $record->signatureDemandeur()) ? route('depenses.signature', $v) : null)
                            ->openUrlInNewTab()
                            ->visible(fn (DemandeDepense $record) => in_array($record->statut, [
                                DemandeDepense::STATUT_APPROUVEE,
                                DemandeDepense::STATUT_PAYEE,
                                DemandeDepense::STATUT_JUSTIFICATION_SOUMISE,
                                DemandeDepense::STATUT_CLOTUREE,
                            ])),
                        Infolists\Components\TextEntry::make('description')
                            ->label('Description')
                            ->placeholder('—')
                            ->columnSpanFull(),
                    ])
                    ->columns(3),

                Infolists\Components\Section::make('Paiement')
                    ->schema([
                        Infolists\Components\TextEntry::make('mode_paiement')
                            ->label('Mode')
                            ->formatStateUsing(fn (?string $state) => DemandeDepense::MODES_PAIEMENT[$state] ?? $state),
                        Infolists\Components\TextEntry::make('reference_paiement')
                            ->label('Référence')
                            ->placeholder('—'),
                        Infolists\Components\TextEntry::make('date_paiement')
                            ->label('Date')
                            ->date('d/m/Y'),
                        Infolists\Components\TextEntry::make('payeePar.name')
                            ->label('Payé par'),
                        Infolists\Components\TextEntry::make('preuve')
                            ->label('Preuve')
                            ->state('Voir la preuve de paiement')
                            ->url(fn (DemandeDepense $record) => route('depenses.preuve-paiement', $record))
                            ->openUrlInNewTab()
                            ->icon('heroicon-o-paper-clip')
                            ->color('primary')
                            ->visible(fn (DemandeDepense $record) => filled($record->preuve_paiement)),
                    ])
                    ->columns(3)
                    ->visible(fn (DemandeDepense $record) => in_array($record->statut, [
                        DemandeDepense::STATUT_PAYEE,
                        DemandeDepense::STATUT_JUSTIFICATION_SOUMISE,
                        DemandeDepense::STATUT_CLOTUREE,
                    ])),
            ]);
    }
}
