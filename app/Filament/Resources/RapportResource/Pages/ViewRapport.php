<?php

namespace App\Filament\Resources\RapportResource\Pages;

use App\Filament\Resources\RapportResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use App\Services\RapportFinancierService;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Support\Enums\FontWeight;
use Illuminate\Support\Number;

class ViewRapport extends ViewRecord
{
    protected static string $resource = RapportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\DeleteAction::make(),
            Actions\Action::make('telecharger')
                ->label('Télécharger')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->url(fn () => route('rapports.telecharger', ['rapport' => $this->record]))
                ->openUrlInNewTab(),
            Actions\Action::make('regenerer')
                ->label('Régénérer')
                ->icon('heroicon-o-arrow-path')
                ->color('warning')
                ->action(function () {
                    // Logique pour régénérer le rapport
                    if ($this->record->type === 'financier') {
                        $service = app(RapportFinancierService::class);
                        
                        if ($this->record->employeur_id) {
                            $entreprise = \App\Models\Entreprise::find($this->record->employeur_id);
                            $rapport = $service->genererRapportFinancier($entreprise, [
                                'date_debut' => $this->record->date_debut,
                                'date_fin' => $this->record->date_fin,
                            ]);
                        } else {
                            $rapport = $service->genererRapportGlobal([
                                'date_debut' => $this->record->date_debut,
                                'date_fin' => $this->record->date_fin,
                            ]);
                        }
                        
                        // Mettre à jour les paramètres avec les nouvelles données
                        $this->record->update([
                            'parametres' => $rapport,
                            'updated_at' => now(),
                        ]);
                        
                        $this->notify('success', 'Rapport régénéré avec succès');
                        $this->redirect(static::getResource()::getUrl('view', ['record' => $this->record]));
                    }
                }),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Informations du rapport')
                    ->schema([
                        Infolists\Components\TextEntry::make('titre')
                            ->label('Titre')
                            ->weight(FontWeight::Bold)
                            ->size(Infolists\Components\TextEntry\TextEntrySize::Large),
                        Infolists\Components\TextEntry::make('description')
                            ->label('Description')
                            ->markdown(),
                        Infolists\Components\Grid::make(3)
                            ->schema([
                                Infolists\Components\TextEntry::make('type')
                                    ->label('Type')
                                    ->badge()
                                    ->color(fn (string $state): string => match ($state) {
                                        'financier' => 'primary',
                                        'presence' => 'success',
                                        'performance' => 'warning',
                                        'retard_absence' => 'danger',
                                        'heures_supplementaires' => 'info',
                                        'conge' => 'secondary',
                                        default => 'gray',
                                    }),
                                Infolists\Components\TextEntry::make('format')
                                    ->label('Format')
                                    ->badge(),
                                Infolists\Components\TextEntry::make('created_at')
                                    ->label('Créé le')
                                    ->dateTime('d/m/Y H:i'),
                            ]),
                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('date_debut')
                                    ->label('Période du')
                                    ->date('d/m/Y'),
                                Infolists\Components\TextEntry::make('date_fin')
                                    ->label('au')
                                    ->date('d/m/Y'),
                            ]),
                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('employeur.nom')
                                    ->label('Employeur')
                                    ->visible(fn ($record) => $record->employeur_id !== null),
                                Infolists\Components\TextEntry::make('departement.nom')
                                    ->label('Département')
                                    ->visible(fn ($record) => $record->departement_id !== null),
                            ]),
                        Infolists\Components\TextEntry::make('createur.name')
                            ->label('Créé par'),
                    ])
                    ->columns(1),
                
                // Section dynamique en fonction du type de rapport
                Infolists\Components\Section::make('Contenu du rapport')
                    ->schema(fn ($record) => $this->getRapportContentSchema($record))
                    ->columns(1),
            ]);
    }

    protected function getRapportContentSchema($record)
    {
        // Retourner différents schémas en fonction du type de rapport
        return match ($record->type) {
            'financier' => $this->getFinancierRapportSchema($record),
            'presence' => $this->getPresenceRapportSchema($record),
            default => [
                Infolists\Components\TextEntry::make('parametres')
                    ->label('Données brutes')
                    ->formatStateUsing(fn ($state) => json_encode($state, JSON_PRETTY_PRINT))
                    ->copyable()
                    ->monospaced(),
            ],
        };
    }

    protected function getFinancierRapportSchema($record)
    {
        $schema = [];
        $parametres = $record->parametres;
        
        // Vérifier si c'est un rapport global ou pour une entreprise spécifique
        $isGlobal = isset($parametres['global']);
        
        if ($isGlobal) {
            // Rapport financier global
            $schema[] = Infolists\Components\Card::make()
                ->schema([
                    Infolists\Components\TextEntry::make('parametres.global.total_facture')
                        ->label('Total facturé')
                        ->formatStateUsing(fn ($state) => Number::currency($state, 'EUR')),
                    Infolists\Components\TextEntry::make('parametres.global.total_paye')
                        ->label('Total payé')
                        ->formatStateUsing(fn ($state) => Number::currency($state, 'EUR')),
                    Infolists\Components\TextEntry::make('parametres.global.total_impaye')
                        ->label('Total impayé')
                        ->formatStateUsing(fn ($state) => Number::currency($state, 'EUR')),
                    Infolists\Components\TextEntry::make('parametres.global.taux_recouvrement')
                        ->label('Taux de recouvrement')
                        ->formatStateUsing(fn ($state) => number_format($state, 2) . '%'),
                ])
                ->columns(4);
            
            // Top entreprises
            $schema[] = Infolists\Components\Section::make('Top 5 des entreprises')
                ->schema([
                    Infolists\Components\RepeatableEntry::make('parametres.top_entreprises')
                        ->schema([
                            Infolists\Components\TextEntry::make('entreprise')
                                ->label('Entreprise'),
                            Infolists\Components\TextEntry::make('montant_total')
                                ->label('Montant total')
                                ->formatStateUsing(fn ($state) => Number::currency($state, 'EUR')),
                            Infolists\Components\TextEntry::make('taux_recouvrement')
                                ->label('Taux de recouvrement')
                                ->formatStateUsing(fn ($state) => number_format($state, 2) . '%'),
                        ])
                        ->columns(3),
                ]);
        } else {
            // Rapport financier pour une entreprise spécifique
            $schema[] = Infolists\Components\Card::make()
                ->schema([
                    Infolists\Components\TextEntry::make('parametres.factures.total')
                        ->label('Total facturé')
                        ->formatStateUsing(fn ($state) => Number::currency($state, 'EUR')),
                    Infolists\Components\TextEntry::make('parametres.factures.paye')
                        ->label('Total payé')
                        ->formatStateUsing(fn ($state) => Number::currency($state, 'EUR')),
                    Infolists\Components\TextEntry::make('parametres.factures.impaye')
                        ->label('Total impayé')
                        ->formatStateUsing(fn ($state) => Number::currency($state, 'EUR')),
                    Infolists\Components\TextEntry::make('parametres.factures.taux_recouvrement')
                        ->label('Taux de recouvrement')
                        ->formatStateUsing(fn ($state) => number_format($state, 2) . '%'),
                ])
                ->columns(4);
            
            // Frais d'usage
            $schema[] = Infolists\Components\Card::make()
                ->schema([
                    Infolists\Components\TextEntry::make('parametres.frais_usage.total')
                        ->label('Total des frais')
                        ->formatStateUsing(fn ($state) => Number::currency($state, 'EUR')),
                    Infolists\Components\TextEntry::make('parametres.frais_usage.facture')
                        ->label('Frais facturés')
                        ->formatStateUsing(fn ($state) => Number::currency($state, 'EUR')),
                    Infolists\Components\TextEntry::make('parametres.frais_usage.non_facture')
                        ->label('Frais non facturés')
                        ->formatStateUsing(fn ($state) => Number::currency($state, 'EUR')),
                ])
                ->columns(3);
        }
        
        // Évolution mensuelle (commun aux deux types de rapports)
        $schema[] = Infolists\Components\Section::make('Évolution mensuelle')
            ->schema([
                Infolists\Components\TextEntry::make('evolution_mensuelle')
                    ->label('Données d\'évolution mensuelle')
                    ->state('Graphique non disponible dans cette vue. Téléchargez le rapport pour visualiser les graphiques.')
                    ->columnSpanFull(),
            ]);
        
        return $schema;
    }

    protected function getPresenceRapportSchema($record)
    {
        // Schéma pour les rapports de présence
        return [
            Infolists\Components\TextEntry::make('parametres')
                ->label('Données du rapport de présence')
                ->formatStateUsing(fn ($state) => json_encode($state, JSON_PRETTY_PRINT))
                ->copyable()
                ->monospaced(),
        ];
    }
}