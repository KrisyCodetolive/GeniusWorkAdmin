<?php

namespace App\Filament\Resources\TaskResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Models\TaskAssignation;
use App\Models\Departement;
use App\Models\Equipe;
use App\Models\Employeur;
use App\Services\Tasks\TaskService;
use Filament\Notifications\Notification;

class AssignationsRelationManager extends RelationManager
{
    protected static string $relationship = 'assignations';

    protected static ?string $recordTitleAttribute = 'id';
   
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('type_assignation')
                    ->label('Type d\'assignation')
                    ->options([
                        'departement' => 'Département',
                        'equipe' => 'Équipe',
                        'employe' => 'Employé',
                        'tous' => 'Tous les employés',
                    ])
                    ->default('employe')
                    ->required()
                    ->reactive(),
                
                Forms\Components\Select::make('departement_id')
                    ->label('Département')
                    ->options(Departement::pluck('nom', 'id'))
                    ->searchable()
                    ->required()
                    ->visible(fn (Forms\Get $get) => $get('type_assignation') === 'departement'),
                
                Forms\Components\Select::make('equipe_id')
                    ->label('Équipe')
                    ->options(Equipe::pluck('nom', 'id'))
                    ->searchable()
                    ->required()
                    ->visible(fn (Forms\Get $get) => $get('type_assignation') === 'equipe'),
                
                Forms\Components\Select::make('employeur_id')
                    ->label('Employé')
                    ->options(Employeur::pluck('nom_complet', 'id'))
                    ->searchable()
                    ->required()
                    ->visible(fn (Forms\Get $get) => $get('type_assignation') === 'employe'),
                
                Forms\Components\Select::make('statut')
                    ->label('Statut')
                    ->options([
                        TaskAssignation::STATUT_EN_ATTENTE => 'En attente',
                        TaskAssignation::STATUT_EN_COURS => 'En cours',
                        TaskAssignation::STATUT_TERMINE => 'Terminée',
                        TaskAssignation::STATUT_EN_RETARD => 'En retard',
                        TaskAssignation::STATUT_ANNULE => 'Annulée',
                    ])
                    ->default(TaskAssignation::STATUT_EN_ATTENTE),
                
                Forms\Components\TextInput::make('progression')
                    ->label('Progression (%)')
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(100)
                    ->default(0),
                
                Forms\Components\DatePicker::make('date_debut_reelle')
                    ->label('Date de début réelle'),
                
                Forms\Components\DatePicker::make('date_fin_reelle')
                    ->label('Date de fin réelle'),
                
                Forms\Components\Textarea::make('commentaire')
                    ->label('Commentaire')
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('employeur.nom_complet')
                    ->label('Employé')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('assignable_type')
                    ->label('Type d\'assignation')
                    ->formatStateUsing(function ($state, $record) {
                        if ($state === null && $record->assignable_id === null) {
                            return 'Globale';
                        }
                        
                        return match ($state) {
                            'App\\Models\\Departement' => 'Département',
                            'App\\Models\\Equipe' => 'Équipe',
                            default => 'Individuelle',
                        };
                    }),
                
                Tables\Columns\TextColumn::make('assignable.nom')
                    ->label('Entité')
                    ->placeholder('N/A'),
                
                Tables\Columns\SelectColumn::make('statut')
                    ->label('Statut')
                    ->options([
                        TaskAssignation::STATUT_EN_ATTENTE => 'En attente',
                        TaskAssignation::STATUT_EN_COURS => 'En cours',
                        TaskAssignation::STATUT_TERMINE => 'Terminée',
                        TaskAssignation::STATUT_EN_RETARD => 'En retard',
                        TaskAssignation::STATUT_ANNULE => 'Annulée',
                    ])
                    ->colors([
                        'secondary' => TaskAssignation::STATUT_EN_ATTENTE,
                        'primary' => TaskAssignation::STATUT_EN_COURS,
                        'success' => TaskAssignation::STATUT_TERMINE,
                        'danger' => TaskAssignation::STATUT_EN_RETARD,
                        'warning' => TaskAssignation::STATUT_ANNULE,
                    ]),
                
                Tables\Columns\TextColumn::make('progression')
                    ->label('Progression')
                    ->formatStateUsing(fn ($state) => $state . '%')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('date_debut_reelle')
                    ->label('Début réel')
                    ->date()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('date_fin_reelle')
                    ->label('Fin réelle')
                    ->date()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('statut')
                    ->label('Statut')
                    ->options([
                        TaskAssignation::STATUT_EN_ATTENTE => 'En attente',
                        TaskAssignation::STATUT_EN_COURS => 'En cours',
                        TaskAssignation::STATUT_TERMINE => 'Terminée',
                        TaskAssignation::STATUT_EN_RETARD => 'En retard',
                        TaskAssignation::STATUT_ANNULE => 'Annulée',
                    ]),
                
                Tables\Filters\Filter::make('progression')
                    ->form([
                        Forms\Components\TextInput::make('progression_min')
                            ->label('Progression minimale (%)')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100),
                        Forms\Components\TextInput::make('progression_max')
                            ->label('Progression maximale (%)')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['progression_min'],
                                fn (Builder $query, $min): Builder => $query->where('progression', '>=', $min),
                            )
                            ->when(
                                $data['progression_max'],
                                fn (Builder $query, $max): Builder => $query->where('progression', '<=', $max),
                            );
                    }),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->using(function (array $data, RelationManager $livewire): TaskAssignation {
                        $task = $livewire->getOwnerRecord();
                        $taskService = app(TaskService::class);
                        
                        // Selon le type d'assignation, utiliser la méthode appropriée du service
                        switch ($data['type_assignation']) {
                            case 'departement':
                                $departement = Departement::find($data['departement_id']);
                                $assignations = $taskService->assignerADepartement($task, $departement);
                                return $assignations->first();
                                
                            case 'equipe':
                                $equipe = Equipe::find($data['equipe_id']);
                                $assignations = $taskService->assignerAEquipe($task, $equipe);
                                return $assignations->first();
                                
                            case 'employe':
                                $employe = Employeur::find($data['employeur_id']);
                                return $taskService->assignerAEmploye($task, $employe);
                                
                            case 'tous':
                                $assignations = $taskService->assignerATous($task, $task->entreprise_id);
                                return $assignations->first();
                                
                            default:
                                throw new \Exception('Type d\'assignation non valide');
                        }
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\Action::make('marquer_terminee')
                    ->label('Marquer comme terminée')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->action(function (TaskAssignation $record) {
                        $record->marquerCommeTermine();
                        
                        Notification::make()
                            ->title('Assignation marquée comme terminée')
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation()
                    ->visible(fn (TaskAssignation $record): bool => $record->statut !== TaskAssignation::STATUT_TERMINE && $record->statut !== TaskAssignation::STATUT_ANNULE),
                
                Tables\Actions\Action::make('update_progression')
                    ->label('Mettre à jour la progression')
                    ->icon('heroicon-o-chart-bar')
                    ->color('primary')
                    ->form([
                        Forms\Components\TextInput::make('progression')
                            ->label('Progression (%)')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100)
                            ->default(fn (TaskAssignation $record): int => $record->progression)
                            ->required(),
                    ])
                    ->action(function (TaskAssignation $record, array $data): void {
                        $record->mettreAJourProgression($data['progression']);
                        
                        Notification::make()
                            ->title('Progression mise à jour')
                            ->success()
                            ->send();
                    })
                    ->visible(fn (TaskAssignation $record): bool => $record->statut !== TaskAssignation::STATUT_TERMINE && $record->statut !== TaskAssignation::STATUT_ANNULE),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('marquer_terminees')
                        ->label('Marquer comme terminées')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(function (\Illuminate\Support\Collection $records): void {
                            foreach ($records as $record) {
                                if ($record->statut !== TaskAssignation::STATUT_TERMINE && $record->statut !== TaskAssignation::STATUT_ANNULE) {
                                    $record->marquerCommeTermine();
                                }
                            }
                            
                            Notification::make()
                                ->title('Assignations marquées comme terminées')
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation(),
                ]),
            ]);
    }
}
