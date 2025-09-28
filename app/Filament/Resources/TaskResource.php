<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TaskResource\Pages;
use App\Filament\Resources\TaskResource\RelationManagers;
use App\Models\Task;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Support\Colors\Color;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Tabs\Tab;

class TaskResource extends Resource
{
    protected static ?string $model = Task::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    
    protected static ?string $navigationGroup = 'Gestion des Tâches';
    
    protected static ?int $navigationSort = 1;
    
    protected static ?string $recordTitleAttribute = 'titre';

    public static function getModelLabel(): string
    {
        return __('Tâche');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Tâches');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Tabs::make('Informations de la tâche')
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
                                
                                Section::make('Dates et timing')
                                    ->schema([
                                        Forms\Components\DatePicker::make('date_debut')
                                            ->label('Date de début')
                                            ->required(),
                                        Forms\Components\DatePicker::make('date_fin')
                                            ->label('Date de fin')
                                            ->required()
                                            ->afterOrEqual('date_debut'),
                                        Forms\Components\TimePicker::make('heure_debut')
                                            ->label('Heure de début')
                                            ->seconds(false),
                                        Forms\Components\TimePicker::make('heure_fin')
                                            ->label('Heure de fin')
                                            ->seconds(false),
                                    ])->columns(2),
                            ]),
                        
                        Tab::make('Détails et classification')
                            ->schema([
                                Section::make('Classification')
                                    ->schema([
                                        Forms\Components\Select::make('statut')
                                            ->label('Statut')
                                            ->options([
                                                Task::STATUT_EN_ATTENTE => 'En attente',
                                                Task::STATUT_EN_COURS => 'En cours',
                                                Task::STATUT_TERMINE => 'Terminée',
                                                Task::STATUT_EN_RETARD => 'En retard',
                                                Task::STATUT_ANNULE => 'Annulée',
                                            ])
                                            ->default(Task::STATUT_EN_ATTENTE)
                                            ->required(),
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
                                    ])->columns(2),
                                
                                Section::make('Détails additionnels')
                                    ->schema([
                                        Forms\Components\Toggle::make('est_livrable')
                                            ->label('Requiert un livrable')
                                            ->default(false),
                                        Forms\Components\RichEditor::make('livrable')
                                            ->label('Description du livrable')
                                            ->columnSpanFull()
                                            ->visible(fn (Forms\Get $get): bool => $get('est_livrable')),
                                    ]),
                            ]),
                        
                        Tab::make('Paramètres de routine')
                            ->schema([
                                Section::make('Configuration de la routine')
                                    ->schema([
                                        Forms\Components\Toggle::make('est_routine')
                                            ->label('Tâche routinière')
                                            ->default(false)
                                            ->reactive(),
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
                                            ->visible(fn (Forms\Get $get): bool => $get('est_routine')),
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
                                            ->visible(fn (Forms\Get $get): bool => $get('est_routine') && $get('frequence_routine') === Task::FREQUENCE_HEBDOMADAIRE),
                                        Forms\Components\Select::make('jour_mois')
                                            ->label('Jour du mois')
                                            ->options(array_combine(range(1, 31), range(1, 31)))
                                            ->visible(fn (Forms\Get $get): bool => $get('est_routine') && in_array($get('frequence_routine'), [Task::FREQUENCE_MENSUELLE, Task::FREQUENCE_TRIMESTRIELLE, Task::FREQUENCE_ANNUELLE])),
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
                                            ->visible(fn (Forms\Get $get): bool => $get('est_routine') && $get('frequence_routine') === Task::FREQUENCE_ANNUELLE),
                                        Forms\Components\DatePicker::make('date_debut_routine')
                                            ->label('Date de début de la routine')
                                            ->visible(fn (Forms\Get $get): bool => $get('est_routine')),
                                        Forms\Components\DatePicker::make('date_fin_routine')
                                            ->label('Date de fin de la routine (optionnel)')
                                            ->visible(fn (Forms\Get $get): bool => $get('est_routine')),
                                        Forms\Components\Toggle::make('active')
                                            ->label('Routine active')
                                            ->default(true)
                                            ->visible(fn (Forms\Get $get): bool => $get('est_routine')),
                                    ])->columns(2),
                            ]),
                        
                        Tab::make('Métadonnées')
                            ->schema([
                                Forms\Components\KeyValue::make('meta_donnees')
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
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('titre')
                    ->label('Titre')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('entreprise.nom')
                    ->label('Entreprise')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('date_debut')
                    ->label('Date de début')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('date_fin')
                    ->label('Date de fin')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('statut')
                    ->label('Statut')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        Task::STATUT_EN_ATTENTE => 'En attente',
                        Task::STATUT_EN_COURS => 'En cours',
                        Task::STATUT_TERMINE => 'Terminée',
                        Task::STATUT_EN_RETARD => 'En retard',
                        Task::STATUT_ANNULE => 'Annulée',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        Task::STATUT_EN_ATTENTE => 'secondary',
                        Task::STATUT_EN_COURS => 'primary',
                        Task::STATUT_TERMINE => 'success',
                        Task::STATUT_EN_RETARD => 'danger',
                        Task::STATUT_ANNULE => 'warning',
                        default => 'gray',
                    }),
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
                Tables\Columns\IconColumn::make('est_routine')
                    ->label('Routine')
                    ->boolean(),
                Tables\Columns\TextColumn::make('createur.name')
                    ->label('Créateur')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Créée le')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Mise à jour le')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('statut')
                    ->label('Statut')
                    ->options([
                        Task::STATUT_EN_ATTENTE => 'En attente',
                        Task::STATUT_EN_COURS => 'En cours',
                        Task::STATUT_TERMINE => 'Terminée',
                        Task::STATUT_EN_RETARD => 'En retard',
                        Task::STATUT_ANNULE => 'Annulée',
                    ]),
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
                Tables\Filters\Filter::make('date_debut')
                    ->form([
                        Forms\Components\DatePicker::make('date_debut_from')
                            ->label('Date de début (depuis)'),
                        Forms\Components\DatePicker::make('date_debut_until')
                            ->label('Date de début (jusqu\'à)'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['date_debut_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('date_debut', '>=', $date),
                            )
                            ->when(
                                $data['date_debut_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('date_debut', '<=', $date),
                            );
                    }),
                Tables\Filters\Filter::make('date_fin')
                    ->form([
                        Forms\Components\DatePicker::make('date_fin_from')
                            ->label('Date de fin (depuis)'),
                        Forms\Components\DatePicker::make('date_fin_until')
                            ->label('Date de fin (jusqu\'à)'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['date_fin_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('date_fin', '>=', $date),
                            )
                            ->when(
                                $data['date_fin_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('date_fin', '<=', $date),
                            );
                    }),
                Tables\Filters\TernaryFilter::make('est_routine')
                    ->label('Tâche routinière'),
                Tables\Filters\TernaryFilter::make('est_livrable')
                    ->label('Requiert un livrable'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('marquer_terminee')
                    ->label('Marquer comme terminée')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->action(function (Task $record) {
                        $record->marquerCommeTermine();
                    })
                    ->requiresConfirmation()
                    ->visible(fn (Task $record): bool => $record->statut !== Task::STATUT_TERMINE && $record->statut !== Task::STATUT_ANNULE),
                Tables\Actions\Action::make('marquer_en_cours')
                    ->label('Marquer comme en cours')
                    ->icon('heroicon-o-play')
                    ->color('primary')
                    ->action(function (Task $record) {
                        $record->update(['statut' => Task::STATUT_EN_COURS]);
                    })
                    ->requiresConfirmation()
                    ->visible(fn (Task $record): bool => $record->statut === Task::STATUT_EN_ATTENTE),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('marquer_terminees')
                        ->label('Marquer comme terminées')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(function (Collection $records) {
                            foreach ($records as $record) {
                                if ($record->statut !== Task::STATUT_TERMINE && $record->statut !== Task::STATUT_ANNULE) {
                                    $record->marquerCommeTermine();
                                }
                            }
                        })
                        ->requiresConfirmation(),
                ]),
            ]);
    }
    
    public static function getRelations(): array
    {
        return [
            RelationManagers\AssignationsRelationManager::class,
            RelationManagers\CommentairesRelationManager::class,
            RelationManagers\FichiersRelationManager::class,
        ];
    }
    
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTasks::route('/'),
            'create' => Pages\CreateTask::route('/create'),
            'view' => Pages\ViewTask::route('/{record}'),
            'edit' => Pages\EditTask::route('/{record}/edit'),
        ];
    }    
}
