<?php

namespace App\Filament\Resources\TaskResource\Pages;

use App\Filament\Resources\TaskResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use App\Models\Task;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Tabs;
use Filament\Infolists\Components\Tabs\Tab;

class ViewTask extends ViewRecord
{
    protected static string $resource = TaskResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\Action::make('marquer_terminee')
                ->label('Marquer comme terminée')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->action(function () {
                    $this->record->marquerCommeTermine();
                    
                    \Filament\Notifications\Notification::make()
                        ->title('Tâche marquée comme terminée')
                        ->success()
                        ->send();
                        
                    $this->redirect($this->getResource()::getUrl('view', ['record' => $this->record]));
                })
                ->requiresConfirmation()
                ->visible(fn () => $this->record->statut !== Task::STATUT_TERMINE && $this->record->statut !== Task::STATUT_ANNULE),
        ];
    }
    
    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Tabs::make('Informations de la tâche')
                    ->tabs([
                        Tab::make('Informations générales')
                            ->schema([
                                Section::make('Informations de base')
                                    ->schema([
                                        Infolists\Components\TextEntry::make('titre')
                                            ->label('Titre'),
                                        Infolists\Components\TextEntry::make('entreprise.nom')
                                            ->label('Entreprise'),
                                        Infolists\Components\TextEntry::make('createur.name')
                                            ->label('Créateur'),
                                        Infolists\Components\TextEntry::make('description')
                                            ->label('Description')
                                            ->html()
                                            ->columnSpanFull(),
                                    ])->columns(2),
                                
                                Section::make('Dates et timing')
                                    ->schema([
                                        Infolists\Components\TextEntry::make('date_debut')
                                            ->label('Date de début')
                                            ->date(),
                                        Infolists\Components\TextEntry::make('date_fin')
                                            ->label('Date de fin')
                                            ->date(),
                                        Infolists\Components\TextEntry::make('heure_debut')
                                            ->label('Heure de début')
                                            ->placeholder('Non définie'),
                                        Infolists\Components\TextEntry::make('heure_fin')
                                            ->label('Heure de fin')
                                            ->placeholder('Non définie'),
                                    ])->columns(2),
                            ]),
                        
                        Tab::make('Détails et classification')
                            ->schema([
                                Section::make('Classification')
                                    ->schema([
                                        Infolists\Components\TextEntry::make('statut')
                                            ->label('Statut')
                                            ->badge()
                                            ->color(fn (string $state): string => match ($state) {
                                                Task::STATUT_EN_ATTENTE => 'gray',
                                                Task::STATUT_EN_COURS => 'primary',
                                                Task::STATUT_TERMINE => 'success',
                                                Task::STATUT_EN_RETARD => 'danger',
                                                Task::STATUT_ANNULE => 'warning',
                                                default => 'gray',
                                            })
                                            ->formatStateUsing(fn (string $state): string => match ($state) {
                                                Task::STATUT_EN_ATTENTE => 'En attente',
                                                Task::STATUT_EN_COURS => 'En cours',
                                                Task::STATUT_TERMINE => 'Terminée',
                                                Task::STATUT_EN_RETARD => 'En retard',
                                                Task::STATUT_ANNULE => 'Annulée',
                                                default => $state,
                                            }),
                                        Infolists\Components\TextEntry::make('priorite')
                                            ->label('Priorité')
                                            ->badge()
                                            ->color(fn (string $state): string => match ($state) {
                                                Task::PRIORITE_BASSE => 'gray',
                                                Task::PRIORITE_BASSE => 'info',
                                                Task::PRIORITE_HAUTE => 'warning',
                                                Task::PRIORITE_URGENTE => 'danger',
                                                default => 'gray',
                                            })
                                            ->formatStateUsing(fn (string $state): string => match ($state) {
                                                Task::PRIORITE_BASSE => 'Basse',
                                                Task::PRIORITE_BASSE => 'Normale',
                                                Task::PRIORITE_HAUTE => 'Haute',
                                                Task::PRIORITE_URGENTE => 'Urgente',
                                                default => $state,
                                            }),
                                        Infolists\Components\TextEntry::make('type')
                                            ->label('Type')
                                            ->badge()
                                            ->formatStateUsing(fn (string $state): string => match ($state) {
                                                Task::TYPE_STANDARD => 'Standard',
                                                Task::TYPE_PROJET => 'Projet',
                                                Task::TYPE_REUNION => 'Réunion',
                                                Task::TYPE_FORMATION => 'Formation',
                                                Task::TYPE_AUTRE => 'Autre',
                                                default => $state,
                                            }),
                                        Infolists\Components\TagsEntry::make('label')
                                            ->label('Étiquettes'),
                                    ])->columns(2),
                                
                                Section::make('Détails additionnels')
                                    ->schema([
                                        Infolists\Components\IconEntry::make('est_livrable')
                                            ->label('Requiert un livrable')
                                            ->boolean(),
                                        Infolists\Components\TextEntry::make('livrable')
                                            ->label('Description du livrable')
                                            ->html()
                                            ->columnSpanFull()
                                            ->visible(fn ($record): bool => $record->est_livrable),
                                    ]),
                            ]),
                        
                        Tab::make('Paramètres de routine')
                            ->schema([
                                Section::make('Configuration de la routine')
                                    ->schema([
                                        Infolists\Components\IconEntry::make('est_routine')
                                            ->label('Tâche routinière')
                                            ->boolean(),
                                        Infolists\Components\TextEntry::make('frequence_routine')
                                            ->label('Fréquence')
                                            ->formatStateUsing(fn (string $state): string => match ($state) {
                                                Task::FREQUENCE_QUOTIDIENNE => 'Quotidienne',
                                                Task::FREQUENCE_HEBDOMADAIRE => 'Hebdomadaire',
                                                Task::FREQUENCE_MENSUELLE => 'Mensuelle',
                                                Task::FREQUENCE_TRIMESTRIELLE => 'Trimestrielle',
                                                Task::FREQUENCE_ANNUELLE => 'Annuelle',
                                                default => $state,
                                            })
                                            ->visible(fn ($record): bool => $record->est_routine),
                                        Infolists\Components\TextEntry::make('jours_semaine')
                                            ->label('Jours de la semaine')
                                            ->visible(fn ($record): bool => $record->est_routine && $record->frequence_routine === Task::FREQUENCE_HEBDOMADAIRE),
                                        Infolists\Components\TextEntry::make('jour_mois')
                                            ->label('Jour du mois')
                                            ->visible(fn ($record): bool => $record->est_routine && in_array($record->frequence_routine, [Task::FREQUENCE_MENSUELLE, Task::FREQUENCE_TRIMESTRIELLE, Task::FREQUENCE_ANNUELLE])),
                                        Infolists\Components\TextEntry::make('mois_annee')
                                            ->label('Mois de l\'année')
                                            ->formatStateUsing(function ($state) {
                                                $mois = [
                                                    '1' => 'Janvier',
                                                    '2' => 'Février',
                                                    '3' => 'Mars',
                                                    '4' => 'Avril',
                                                    '5' => 'Mai',
                                                    '6' => 'Juin',
                                                    '7' => 'Juillet',
                                                    '8' => 'Août',
                                                    '9' => 'Septembre',
                                                    '10' => 'Octobre',
                                                    '11' => 'Novembre',
                                                    '12' => 'Décembre',
                                                ];
                                                return $mois[$state] ?? $state;
                                            })
                                            ->visible(fn ($record): bool => $record->est_routine && $record->frequence_routine === Task::FREQUENCE_ANNUELLE),
                                        Infolists\Components\TextEntry::make('date_debut_routine')
                                            ->label('Date de début de la routine')
                                            ->date()
                                            ->visible(fn ($record): bool => $record->est_routine),
                                        Infolists\Components\TextEntry::make('date_fin_routine')
                                            ->label('Date de fin de la routine')
                                            ->date()
                                            ->visible(fn ($record): bool => $record->est_routine),
                                        Infolists\Components\IconEntry::make('active')
                                            ->label('Routine active')
                                            ->boolean()
                                            ->visible(fn ($record): bool => $record->est_routine),
                                    ])->columns(2),
                            ]),
                        
                        Tab::make('Métadonnées')
                            ->schema([
                                Infolists\Components\KeyValueEntry::make('metadata')
                                    ->label('Métadonnées')
                                    ->columnSpanFull(),
                            ]),
                    ])->columnSpanFull(),
            ]);
    }
}
