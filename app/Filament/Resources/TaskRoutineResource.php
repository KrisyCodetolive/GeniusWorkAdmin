<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TaskRoutineResource\Pages;
use App\Models\Task;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Tabs\Tab;

class TaskRoutineResource extends Resource
{
    protected static ?string $model = Task::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrows-right-left';
    
    protected static ?string $navigationGroup = 'Gestion des Tâches';
    
    protected static ?int $navigationSort = 2;
    
    protected static ?string $recordTitleAttribute = 'titre';
    
    protected static ?string $slug = 'task-routines';

    public static function getModelLabel(): string
    {
        return __('Tâche Routinière');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Tâches Routinières');
    }
    
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('est_routine', true);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Tabs::make('Informations de la tâche routinière')
                    ->tabs([
                        Tab::make('Informations générales')
                            ->schema([
                                Section::make('Informations de base')
                                    ->schema([
                                        Forms\Components\TextInput::make('titre')
                                            ->label('Titre')
                                            ->required()
                                            ->maxLength(255),
                                        Forms\Components\Select::make('entreprise_id')
                                            ->label('Entreprise')
                                            ->relationship('entreprise', 'nom')
                                            ->required()
                                            ->searchable(),
                                        Forms\Components\Select::make('createur_id')
                                            ->label('Créateur')
                                            ->relationship('createur', 'name')
                                            ->required()
                                            ->searchable(),
                                        Forms\Components\RichEditor::make('description')
                                            ->label('Description')
                                            ->columnSpanFull(),
                                    ])->columns(2),
                                
                                Section::make('Classification')
                                    ->schema([
                                        Forms\Components\Select::make('priorite')
                                            ->label('Priorité')
                                            ->options([
                                                Task::PRIORITE_BASSE => 'Basse',
                                                Task::PRIORITE_BASSE => 'Normale',
                                                Task::PRIORITE_HAUTE => 'Haute',
                                                Task::PRIORITE_URGENTE => 'Urgente',
                                            ])
                                            ->default(Task::PRIORITE_BASSE)
                                            ->required(),
                                        Forms\Components\Select::make('type')
                                            ->label('Type')
                                            ->options([
                                                Task::TYPE_STANDARD => 'Standard',
                                                Task::TYPE_PROJET => 'Projet',
                                                Task::TYPE_REUNION => 'Réunion',
                                                Task::TYPE_FORMATION => 'Formation',
                                                Task::TYPE_AUTRE => 'Autre',
                                            ])
                                            ->default(Task::TYPE_STANDARD)
                                            ->required(),
                                        Forms\Components\TagsInput::make('label')
                                            ->label('Étiquettes')
                                            ->separator(','),
                                    ])->columns(3),
                            ]),
                        
                        Tab::make('Configuration de la routine')
                            ->schema([
                                Section::make('Paramètres de la routine')
                                    ->schema([
                                        Forms\Components\Hidden::make('est_routine')
                                            ->default(true),
                                        Forms\Components\Select::make('frequence_routine')
                                            ->label('Fréquence')
                                            ->options([
                                                Task::FREQUENCE_QUOTIDIENNE => 'Quotidienne',
                                                Task::FREQUENCE_HEBDOMADAIRE => 'Hebdomadaire',
                                                Task::FREQUENCE_MENSUELLE => 'Mensuelle',
                                                Task::FREQUENCE_TRIMESTRIELLE => 'Trimestrielle',
                                                Task::FREQUENCE_ANNUELLE => 'Annuelle',
                                            ])
                                            ->default(Task::FREQUENCE_QUOTIDIENNE)
                                            ->required()
                                            ->reactive(),
                                        Forms\Components\Select::make('jours_semaine')
                                            ->label('Jours de la semaine')
                                            ->options([
                                                'lundi' => 'Lundi',
                                                'mardi' => 'Mardi',
                                                'mercredi' => 'Mercredi',
                                                'jeudi' => 'Jeudi',
                                                'vendredi' => 'Vendredi',
                                                'samedi' => 'Samedi',
                                                'dimanche' => 'Dimanche',
                                            ])
                                            ->multiple()
                                            ->visible(fn (Forms\Get $get): bool => $get('frequence_routine') === Task::FREQUENCE_HEBDOMADAIRE),
                                        Forms\Components\Select::make('jour_mois')
                                            ->label('Jour du mois')
                                            ->options(array_combine(range(1, 31), range(1, 31)))
                                            ->visible(fn (Forms\Get $get): bool => in_array($get('frequence_routine'), [Task::FREQUENCE_MENSUELLE, Task::FREQUENCE_TRIMESTRIELLE, Task::FREQUENCE_ANNUELLE])),
                                        Forms\Components\Select::make('mois_annee')
                                            ->label('Mois de l\'année')
                                            ->options([
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
                                            ])
                                            ->visible(fn (Forms\Get $get): bool => $get('frequence_routine') === Task::FREQUENCE_ANNUELLE),
                                    ])->columns(2),
                                
                                Section::make('Période d\'activité')
                                    ->schema([
                                        Forms\Components\DatePicker::make('date_debut_routine')
                                            ->label('Date de début de la routine')
                                            ->required(),
                                        Forms\Components\DatePicker::make('date_fin_routine')
                                            ->label('Date de fin de la routine (optionnel)'),
                                        Forms\Components\Toggle::make('active')
                                            ->label('Routine active')
                                            ->default(true),
                                    ])->columns(3),
                                
                                Section::make('Détails des tâches générées')
                                    ->schema([
                                        Forms\Components\TimePicker::make('heure_debut')
                                            ->label('Heure de début')
                                            ->seconds(false),
                                        Forms\Components\TimePicker::make('heure_fin')
                                            ->label('Heure de fin')
                                            ->seconds(false),
                                        Forms\Components\Toggle::make('est_livrable')
                                            ->label('Requiert un livrable')
                                            ->default(false),
                                        Forms\Components\RichEditor::make('livrable')
                                            ->label('Description du livrable')
                                            ->columnSpanFull()
                                            ->visible(fn (Forms\Get $get): bool => $get('est_livrable')),
                                    ])->columns(3),
                            ]),
                        
                        Tab::make('Métadonnées')
                            ->schema([
                                Forms\Components\KeyValue::make('metadata')
                                    ->label('Métadonnées')
                                    ->columnSpanFull(),
                            ]),
                    ])->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('titre')
                    ->label('Titre')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('entreprise.nom')
                    ->label('Entreprise')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('frequence_routine')
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
                Tables\Columns\TextColumn::make('date_debut_routine')
                    ->label('Début')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('date_fin_routine')
                    ->label('Fin')
                    ->date()
                    ->placeholder('Sans fin')
                    ->sortable(),
                Tables\Columns\IconColumn::make('active')
                    ->label('Active')
                    ->boolean(),
                Tables\Columns\TextColumn::make('priorite')
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
                Tables\Columns\TextColumn::make('type')
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
                Tables\Columns\TextColumn::make('createur.name')
                    ->label('Créateur')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('frequence_routine')
                    ->label('Fréquence')
                    ->options([
                        Task::FREQUENCE_QUOTIDIENNE => 'Quotidienne',
                        Task::FREQUENCE_HEBDOMADAIRE => 'Hebdomadaire',
                        Task::FREQUENCE_MENSUELLE => 'Mensuelle',
                        Task::FREQUENCE_TRIMESTRIELLE => 'Trimestrielle',
                        Task::FREQUENCE_ANNUELLE => 'Annuelle',
                    ]),
                Tables\Filters\TernaryFilter::make('active')
                    ->label('Active'),
                Tables\Filters\SelectFilter::make('priorite')
                    ->label('Priorité')
                    ->options([
                        Task::PRIORITE_BASSE => 'Basse',
                        Task::PRIORITE_BASSE => 'Normale',
                        Task::PRIORITE_HAUTE => 'Haute',
                        Task::PRIORITE_URGENTE => 'Urgente',
                    ]),
                Tables\Filters\SelectFilter::make('type')
                    ->label('Type')
                    ->options([
                        Task::TYPE_STANDARD => 'Standard',
                        Task::TYPE_PROJET => 'Projet',
                        Task::TYPE_REUNION => 'Réunion',
                        Task::TYPE_FORMATION => 'Formation',
                        Task::TYPE_AUTRE => 'Autre',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('activer')
                    ->label('Activer')
                    ->icon('heroicon-o-play')
                    ->color('success')
                    ->action(function (Task $record) {
                        $record->update(['active' => true]);
                        
                        \Filament\Notifications\Notification::make()
                            ->title('Routine activée')
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation()
                    ->visible(fn (Task $record): bool => !$record->active),
                
                Tables\Actions\Action::make('desactiver')
                    ->label('Désactiver')
                    ->icon('heroicon-o-pause')
                    ->color('danger')
                    ->action(function (Task $record) {
                        $record->update(['active' => false]);
                        
                        \Filament\Notifications\Notification::make()
                            ->title('Routine désactivée')
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation()
                    ->visible(fn (Task $record): bool => $record->active),
                
                Tables\Actions\Action::make('generer_maintenant')
                    ->label('Générer maintenant')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->action(function (Task $record) {
                        $taskRoutineService = app(\App\Services\Tasks\TaskRoutineService::class);
                        $taskRoutineService->genererTacheDepuisRoutine($record);
                        
                        \Filament\Notifications\Notification::make()
                            ->title('Tâche générée manuellement')
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation()
                    ->visible(fn (Task $record): bool => $record->active),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('activer_routines')
                        ->label('Activer les routines')
                        ->icon('heroicon-o-play')
                        ->color('success')
                        ->action(function (\Illuminate\Support\Collection $records) {
                            foreach ($records as $record) {
                                $record->update(['active' => true]);
                            }
                            
                            \Filament\Notifications\Notification::make()
                                ->title('Routines activées')
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation(),
                    
                    Tables\Actions\BulkAction::make('desactiver_routines')
                        ->label('Désactiver les routines')
                        ->icon('heroicon-o-pause')
                        ->color('danger')
                        ->action(function (\Illuminate\Support\Collection $records) {
                            foreach ($records as $record) {
                                $record->update(['active' => false]);
                            }
                            
                            \Filament\Notifications\Notification::make()
                                ->title('Routines désactivées')
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation(),
                ]),
            ]);
    }
    
    public static function getRelations(): array
    {
        return [
            TaskResource\RelationManagers\AssignationsRelationManager::class,
        ];
    }
    
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTaskRoutines::route('/'),
            'create' => Pages\CreateTaskRoutine::route('/create'),
            'view' => Pages\ViewTaskRoutine::route('/{record}'),
            'edit' => Pages\EditTaskRoutine::route('/{record}/edit'),
        ];
    }    
}
