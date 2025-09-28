<?php

namespace App\Filament\Resources\TaskRoutineResource\Pages;

use App\Filament\Resources\TaskRoutineResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use App\Models\Task;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Tabs;
use Filament\Infolists\Components\Tabs\Tab;

class ViewTaskRoutine extends ViewRecord
{
    protected static string $resource = TaskRoutineResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\Action::make('activer')
                ->label('Activer')
                ->icon('heroicon-o-play')
                ->color('success')
                ->action(function () {
                    $this->record->update(['active' => true]);
                    
                    \Filament\Notifications\Notification::make()
                        ->title('Routine activée')
                        ->success()
                        ->send();
                        
                    $this->redirect($this->getResource()::getUrl('view', ['record' => $this->record]));
                })
                ->requiresConfirmation()
                ->visible(fn () => !$this->record->active),
            
            Actions\Action::make('desactiver')
                ->label('Désactiver')
                ->icon('heroicon-o-pause')
                ->color('danger')
                ->action(function () {
                    $this->record->update(['active' => false]);
                    
                    \Filament\Notifications\Notification::make()
                        ->title('Routine désactivée')
                        ->success()
                        ->send();
                        
                    $this->redirect($this->getResource()::getUrl('view', ['record' => $this->record]));
                })
                ->requiresConfirmation()
                ->visible(fn () => $this->record->active),
            
            Actions\Action::make('generer_maintenant')
                ->label('Générer maintenant')
                ->icon('heroicon-o-arrow-path')
                ->color('warning')
                ->action(function () {
                    $taskRoutineService = app(\App\Services\Tasks\TaskRoutineService::class);
                    $taskRoutineService->genererTacheDepuisRoutine($this->record);
                    
                    \Filament\Notifications\Notification::make()
                        ->title('Tâche générée manuellement')
                        ->success()
                        ->send();
                })
                ->requiresConfirmation()
                ->visible(fn () => $this->record->active),
        ];
    }
    
    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Tabs::make('Informations de la tâche routinière')
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
                                
                                Section::make('Classification')
                                    ->schema([
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
                                    ])->columns(3),
                            ]),
                        
                        Tab::make('Configuration de la routine')
                            ->schema([
                                Section::make('Paramètres de la routine')
                                    ->schema([
                                        Infolists\Components\TextEntry::make('frequence_routine')
                                            ->label('Fréquence')
                                            ->badge()
                                            ->formatStateUsing(fn (string $state): string => match ($state) {
                                                Task::FREQUENCE_QUOTIDIENNE => 'Quotidienne',
                                                Task::FREQUENCE_HEBDOMADAIRE => 'Hebdomadaire',
                                                Task::FREQUENCE_MENSUELLE => 'Mensuelle',
                                                Task::FREQUENCE_TRIMESTRIELLE => 'Trimestrielle',
                                                Task::FREQUENCE_ANNUELLE => 'Annuelle',
                                                default => $state,
                                            }),
                                        Infolists\Components\TextEntry::make('jours_semaine')
                                            ->label('Jours de la semaine')
                                            ->visible(fn ($record): bool => $record->frequence_routine === Task::FREQUENCE_HEBDOMADAIRE),
                                        Infolists\Components\TextEntry::make('jour_mois')
                                            ->label('Jour du mois')
                                            ->visible(fn ($record): bool => in_array($record->frequence_routine, [Task::FREQUENCE_MENSUELLE, Task::FREQUENCE_TRIMESTRIELLE, Task::FREQUENCE_ANNUELLE])),
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
                                            ->visible(fn ($record): bool => $record->frequence_routine === Task::FREQUENCE_ANNUELLE),
                                    ])->columns(2),
                                
                                Section::make('Période d\'activité')
                                    ->schema([
                                        Infolists\Components\TextEntry::make('date_debut_routine')
                                            ->label('Date de début de la routine')
                                            ->date(),
                                        Infolists\Components\TextEntry::make('date_fin_routine')
                                            ->label('Date de fin de la routine')
                                            ->date()
                                            ->placeholder('Sans fin'),
                                        Infolists\Components\IconEntry::make('active')
                                            ->label('Routine active')
                                            ->boolean(),
                                    ])->columns(3),
                                
                                Section::make('Détails des tâches générées')
                                    ->schema([
                                        Infolists\Components\TextEntry::make('heure_debut')
                                            ->label('Heure de début')
                                            ->placeholder('Non définie'),
                                        Infolists\Components\TextEntry::make('heure_fin')
                                            ->label('Heure de fin')
                                            ->placeholder('Non définie'),
                                        Infolists\Components\IconEntry::make('est_livrable')
                                            ->label('Requiert un livrable')
                                            ->boolean(),
                                        Infolists\Components\TextEntry::make('livrable')
                                            ->label('Description du livrable')
                                            ->html()
                                            ->columnSpanFull()
                                            ->visible(fn ($record): bool => $record->est_livrable),
                                    ])->columns(3),
                            ]),
                        
                        Tab::make('Métadonnées')
                            ->schema([
                                Infolists\Components\KeyValueEntry::make('meta_donnees')
                                    ->label('Métadonnées')
                                    ->visible(fn ($record): bool => is_array($record->meta_donnees) || is_object($record->meta_donnees))
                                    ->columnSpanFull(),
                            ]),
                    ])->columnSpanFull(),
            ]);
    }
}
