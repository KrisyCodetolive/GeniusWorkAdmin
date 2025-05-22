<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PlageHoraireBaseResource\Pages;
use App\Filament\Resources\PlageHoraireBaseResource\RelationManagers;
use App\Models\PlageHoraire;
use App\Models\Entreprise;
use App\Models\Employeur;
use App\Models\Jour;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\KeyValue;
use Carbon\Carbon;

class PlageHoraireBaseResource extends Resource
{
    protected static ?string $model = PlageHoraire::class;

    protected static ?string $navigationIcon = 'heroicon-o-clock';
    
    protected static ?string $navigationGroup = 'Structure Organisationnelle';

    protected static ?int $navigationSort = 4;

    protected static ?string $recordTitleAttribute = 'nom';
    
    protected static ?string $modelLabel = 'Plage horaire';
    
    protected static ?string $pluralModelLabel = 'Plages horaires';

    public static function form(Form $form): Form
    {
        $user = auth()->user();
        $isAdminOrHigher = $user->isSuperAdmin() || $user->isSupport();

        return $form
            ->schema([
                Section::make('Informations de base')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('entreprise_id')
                                    ->label('Entreprise')
                                    ->options(Entreprise::pluck('nom', 'id'))
                                    ->searchable()
                                    ->required()
                                    ->visible($isAdminOrHigher)
                                    ->default(fn () => $user->isSuperAdmin() || $user->isSupport() ? null : $user->entreprise_id),
                                TextInput::make('nom')
                                    ->label('Nom')
                                    ->required()
                                    ->maxLength(255),
                                Select::make('statut')
                                    ->label('Statut')
                                    ->options([
                                        'actif' => 'Actif',
                                        'inactif' => 'Inactif',
                                    ])
                                    ->default('actif')
                                    ->required(),
                                TimePicker::make('heure_debut')
                                    ->label('Heure de début par défaut')
                                    ->seconds(false)
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function ($state, $set, $get) {
                                        // Mettre à jour les heures de début des jours de travail
                                        $joursTravail = $get('jours_travail') ?? [];
                                        foreach ($joursTravail as $key => $jour) {
                                            if (empty($jour['heure_debut'])) {
                                                $joursTravail[$key]['heure_debut'] = $state;
                                            }
                                        }
                                        $set('jours_travail', $joursTravail);
                                    }),
                                TimePicker::make('heure_fin')
                                    ->label('Heure de fin par défaut')
                                    ->seconds(false)
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function ($state, $set, $get) {
                                        // Mettre à jour les heures de fin des jours de travail
                                        $joursTravail = $get('jours_travail') ?? [];
                                        foreach ($joursTravail as $key => $jour) {
                                            if (empty($jour['heure_fin'])) {
                                                $joursTravail[$key]['heure_fin'] = $state;
                                            }
                                        }
                                        $set('jours_travail', $joursTravail);
                                    }),
                                TextInput::make('duree_pause')
                                    ->label('Durée de pause par défaut (minutes)')
                                    ->numeric()
                                    ->minValue(0),
                                Toggle::make('est_standard')
                                    ->label('Est standard')
                                    ->default(true),
                                Toggle::make('est_flexible')
                                    ->label('Est flexible')
                                    ->default(false),
                                TextInput::make('marge_retard')
                                    ->label('Marge de retard (minutes)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->default(0),
                                TextInput::make('marge_depart')
                                    ->label('Marge de départ (minutes)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->default(0),
                            ]),
                        Textarea::make('description')
                            ->label('Description')
                            ->maxLength(1000)
                            ->columnSpanFull(),
                    ]),
                Section::make('Jours de travail')
                    ->schema([
                        Repeater::make('jours_travail')
                            ->label('Jours de travail')
                            ->schema([
                                Select::make('jour_semaine')
                                    ->label('Jour de la semaine')
                                    ->options([
                                        'Lundi' => 'Lundi',
                                        'Mardi' => 'Mardi',
                                        'Mercredi' => 'Mercredi',
                                        'Jeudi' => 'Jeudi',
                                        'Vendredi' => 'Vendredi',
                                        'Samedi' => 'Samedi',
                                        'Dimanche' => 'Dimanche',
                                    ])
                                    ->required(),
                                TimePicker::make('heure_debut')
                                    ->label('Heure de début')
                                    ->seconds(false)
                                    ->placeholder(function ($get) {
                                        return 'Utilise l\'heure par défaut';
                                    })
                                    ->afterStateHydrated(function ($state, $set, $get, $record) {
                                        if (empty($state) && $record) {
                                            $set('heure_debut', $record->heure_debut);
                                        }
                                    }),
                                TimePicker::make('heure_fin')
                                    ->label('Heure de fin')
                                    ->seconds(false)
                                    ->placeholder(function ($get) {
                                        return 'Utilise l\'heure par défaut';
                                    })
                                    ->afterStateHydrated(function ($state, $set, $get, $record) {
                                        if (empty($state) && $record) {
                                            $set('heure_fin', $record->heure_fin);
                                        }
                                    }),
                                Toggle::make('est_travaille')
                                    ->label('Est un jour travaillé')
                                    ->default(true),
                                Toggle::make('est_ferie')
                                    ->label('Est un jour férié')
                                    ->default(false),
                            ])
                            ->columns(3)
                            ->defaultItems(7) // Pour les 7 jours de la semaine
                            ->reorderable()
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => 
                                $state['jour_semaine'] ?? null
                            ),
                    ]),
                Section::make('Configuration avancée')
                    ->schema([
                        KeyValue::make('pauses')
                            ->label('Pauses')
                            ->keyLabel('Heure')
                            ->valueLabel('Durée (minutes)')
                            ->reorderable(),
                        KeyValue::make('configuration')
                            ->label('Configuration')
                            ->keyLabel('Clé')
                            ->valueLabel('Valeur')
                            ->reorderable()
                            ->columnSpanFull(),
                    ])
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        $user = auth()->user();
        $isAdminOrHigher = $user->isSuperAdmin() || $user->isSupport();

        return $table
            ->columns([
                Tables\Columns\TextColumn::make('entreprise.nom')
                    ->label('Entreprise')
                    ->searchable()
                    ->sortable()
                    ->visible($isAdminOrHigher),
                Tables\Columns\TextColumn::make('nom')
                    ->label('Nom')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('statut')
                    ->label('Statut')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'actif' => 'primary',
                        'inactif' => 'warning',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('heure_debut')
                    ->label('Début')
                    ->formatStateUsing(fn ($state) => $state ? Carbon::parse($state)->format('H:i') : '-')
                    ->sortable(),
                Tables\Columns\TextColumn::make('heure_fin')
                    ->label('Fin')
                    ->formatStateUsing(fn ($state) => $state ? Carbon::parse($state)->format('H:i') : '-')
                    ->sortable(),
                Tables\Columns\TextColumn::make('duree')
                    ->label('Durée')
                    ->getStateUsing(fn ($record) => $record->getDureeFormatee()),
                Tables\Columns\TextColumn::make('jours_count')
                    ->label('Jours de travail')
                    ->getStateUsing(function ($record) {
                        $joursTravail = $record->jours_travail ?? [];
                        if (is_string($joursTravail)) {
                            $joursTravail = json_decode($joursTravail, true) ?? [];
                        }
                        return count(array_filter($joursTravail, fn($jour) => $jour['est_travaille'] ?? false));
                    }),
                Tables\Columns\TextColumn::make('employes_count')
                    ->label('Employés')
                    ->counts('jours')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Mis à jour le')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('entreprise_id')
                    ->label('Entreprise')
                    ->options(Entreprise::pluck('nom', 'id'))
                    ->searchable()
                    ->visible($isAdminOrHigher),
                SelectFilter::make('statut')
                    ->label('Statut')
                    ->options([
                        'actif' => 'Actif',
                        'inactif' => 'Inactif',
                    ]),
                TernaryFilter::make('est_standard')
                    ->label('Standard'),
                TernaryFilter::make('est_flexible')
                    ->label('Flexible'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\Action::make('assignerEmployes')
                    ->label('Assigner aux employés')
                    ->icon('heroicon-o-user-group')
                    ->form([
                        Select::make('employeurs')
                            ->label('Employés')
                            ->multiple()
                            ->options(function (PlageHoraire $record) {
                                $user = auth()->user();
                                $query = Employeur::query();
                                
                                // Si l'utilisateur n'est pas SuperAdmin ou Support, filtrer par entreprise
                                if (!$user->isSuperAdmin() && !$user->isSupport()) {
                                    $query->where('entreprise_id', $user->entreprise_id);
                                } else if ($record->entreprise_id) {
                                    // Sinon, filtrer par l'entreprise de la plage horaire
                                    $query->where('entreprise_id', $record->entreprise_id);
                                }
                                
                                return $query->pluck('nom', 'id');
                            })
                            ->preload()
                            ->searchable()
                            ->required(),
                    ])
                    ->action(function (PlageHoraire $record, array $data) {
                        $user = auth()->user();
                        $entrepriseId = $record->entreprise_id;
                       
                        
                        foreach ($data['employeurs'] as $employeurId) {
                            // Vérifier si l'association n'existe pas déjà
                            $jourExistant = Jour::where('employeur_id', $employeurId)
                                ->where('plage_horaire_id', $record->id)
                                ->first();
                            
                            if (!$jourExistant) {
                                // Créer le jour pour l'employé
                                Jour::create([
                                    'employeur_id' => $employeurId,
                                    'plage_horaire_id' => $record->id,
                                    'entreprise_id' => $entrepriseId,
                                ]);
                            }
                        }
                    })
                    ->successNotificationTitle('Employés assignés avec succès'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
                Tables\Actions\Action::make('genererExemples')
                    ->label('Générer des exemples')
                    ->icon('heroicon-o-clock')
                    ->visible(fn () => auth()->user()->entreprise_id && !auth()->user()->isSuperAdmin() && !auth()->user()->isSupport())
                    ->action(function () {
                        $user = auth()->user();
                        $entreprise = $user->entreprise;
                        
                        if (!$entreprise) {
                            Filament\Notifications\Notification::make()
                                ->title('Erreur')
                                ->body('Vous devez être associé à une entreprise pour générer des exemples de plages horaires.')
                                ->danger()
                                ->send();
                            return;
                        }
                        
                        // Générer les exemples de plages horaires
                        $result = \Database\Seeders\PlageHoraireExempleSeeder::createForEntreprise($entreprise);
                        
                        // Notification de succès
                        Filament\Notifications\Notification::make()
                            ->title('Exemples générés')
                            ->body(count($result['created']) . ' plages horaires exemples ont été créées pour votre entreprise.' . 
                                   ($result['existants'] > 0 ? ' ' . $result['existants'] . ' plages horaires existaient déjà.' : ''))
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Générer des exemples de plages horaires')
                    ->modalDescription('Cette action va créer des plages horaires exemples pour votre entreprise. Vous pourrez les modifier ou les supprimer par la suite.')
                    ->modalSubmitActionLabel('Générer'),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\JoursRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPlageHoraireBases::route('/'),
            'create' => Pages\CreatePlageHoraireBase::route('/create'),
            'edit' => Pages\EditPlageHoraireBase::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
