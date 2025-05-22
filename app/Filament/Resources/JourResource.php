<?php

namespace App\Filament\Resources;

use App\Filament\Resources\JourResource\Pages;
use App\Filament\Resources\JourResource\RelationManagers;
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
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\KeyValue;
use Carbon\Carbon;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Actions;
use App\Models\PlageHoraire;
use App\Models\Employeur;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\ColorPicker;
use Illuminate\Support\Collection;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\Action as TableAction;
use Filament\Support\Enums\ActionSize;
use Filament\Forms\Components\CheckboxList;

class JourResource extends Resource
{
    protected static ?string $model = Jour::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar';
    
    // Caché dans le menu de navigation mais accessible via PlageHoraireBaseResource
    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $recordTitleAttribute = 'id';
    
    protected static ?string $modelLabel = 'Planning journalier';
    
    protected static ?string $pluralModelLabel = 'Plannings journaliers';

    public static function form(Form $form): Form
    {
        $user = auth()->user();
        $isSuperAdminOrSupport = $user->isSuperAdmin() || $user->isSupport();

        return $form
            ->schema([
                Section::make('Informations du jour')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('entreprise_id')
                                    ->label('Entreprise')
                                    ->relationship('entreprise', 'nom')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->visible($isSuperAdminOrSupport),
                                Select::make('employeur_id')
                                    ->label('Employé')
                                    ->relationship('employeur', 'nom', function ($query) use ($user, $isSuperAdminOrSupport) {
                                        if (!$isSuperAdminOrSupport) {
                                            $query->where('entreprise_id', $user->entreprise_id);
                                        }
                                    })
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function ($state, callable $set) {
                                        if ($state) {
                                            $employeur = Employeur::find($state);
                                            if ($employeur) {
                                                $set('entreprise_id', $employeur->entreprise_id);
                                            }
                                        }
                                    }),
                                Select::make('plage_horaire_id')
                                    ->label('Plage horaire')
                                    ->relationship('plageHoraire', 'nom', function ($query) use ($user, $isSuperAdminOrSupport) {
                                        if (!$isSuperAdminOrSupport) {
                                            $query->where('entreprise_id', $user->entreprise_id);
                                        }
                                    })
                                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->nom . ' (' . ($record->getPlageFormatee() ?? 'Horaire non défini') . ')')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->suffixAction(
                                        Action::make('createPlageHoraire')
                                            ->icon('heroicon-m-plus-circle')
                                            ->tooltip('Créer une nouvelle plage horaire')
                                            ->form([
                                                Grid::make(2)
                                                    ->schema([
                                                        Select::make('entreprise_id')
                                                            ->label('Entreprise')
                                                            ->relationship('entreprise', 'nom')
                                                            ->searchable()
                                                            ->preload()
                                                            ->required()
                                                            ->visible($isSuperAdminOrSupport),
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
                                                            ->label('Heure de début')
                                                            ->seconds(false)
                                                            ->required(),
                                                        TimePicker::make('heure_fin')
                                                            ->label('Heure de fin')
                                                            ->seconds(false)
                                                            ->required(),
                                                        TextInput::make('duree_pause')
                                                            ->label('Durée de pause (minutes)')
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
                                            ])
                                            ->action(function (array $data, Select $component) {
                                                $user = auth()->user();
                                                $entrepriseId = $user->entreprise_id;
                                                
                                                // Si l'utilisateur est SuperAdmin ou Support, il peut créer des plages horaires pour n'importe quelle entreprise
                                                if ($user->isSuperAdmin() || $user->isSupport()) {
                                                    $entrepriseId = $data['entreprise_id'] ?? $entrepriseId;
                                                }
                                                
                                                $plageHoraire = PlageHoraire::create([
                                                    'nom' => $data['nom'],
                                                    'statut' => $data['statut'],
                                                    'heure_debut' => $data['heure_debut'],
                                                    'heure_fin' => $data['heure_fin'],
                                                    'duree_pause' => $data['duree_pause'] ?? null,
                                                    'est_standard' => $data['est_standard'],
                                                    'est_flexible' => $data['est_flexible'],
                                                    'marge_retard' => $data['marge_retard'],
                                                    'marge_depart' => $data['marge_depart'],
                                                    'description' => $data['description'] ?? null,
                                                    'entreprise_id' => $entrepriseId,
                                                ]);
                                                
                                                $component->state($plageHoraire->id);
                                                
                                                Notification::make()
                                                    ->title('Plage horaire créée avec succès')
                                                    ->success()
                                                    ->send();
                                            })
                                    ),
                                ])
                            ])
                        ]);
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = auth()->user();
        
        // Si l'utilisateur n'est pas SuperAdmin ou Support, on utilise son entreprise_id
        if (!$user->isSuperAdmin() && !$user->isSupport()) {
            $data['entreprise_id'] = $user->entreprise_id;
        } else if (!isset($data['entreprise_id'])) {
            // Pour les SuperAdmin et Support, si l'entreprise n'est pas spécifiée,
            // on utilise l'entreprise de l'employé sélectionné
            if (isset($data['employeur_id'])) {
                $employeur = Employeur::find($data['employeur_id']);
                if ($employeur) {
                    $data['entreprise_id'] = $employeur->entreprise_id;
                }
            }
        }
        
        return $data;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('plageHoraire.nom')
                    ->label('Plage horaire')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('employeur.nom')
                    ->label('Employé')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('horaire')
                    ->label('Horaire')
                    ->getStateUsing(function ($record) {
                        $heureDebut = $record->plageHoraire?->getHeureDebutFormatee() ?? '--';
                        $heureFin = $record->plageHoraire?->getHeureFinFormatee() ?? '--';
                        return $heureDebut . ' - ' . $heureFin;
                    })
                    ->searchable(false)
                    ->sortable(false),
                Tables\Columns\TextColumn::make('duree')
                    ->label('Durée')
                    ->getStateUsing(function ($record) {
                        if (!$record->plageHoraire) {
                            return '--:--';
                        }
                        
                        return $record->plageHoraire->getDureeFormatee() ?? '--:--';
                    })
                    ->searchable(false)
                    ->sortable(false),
                Tables\Columns\TextColumn::make('presences_count')
                    ->label('Présences')
                    ->counts('presences')
                    ->sortable(),
                Tables\Columns\TextColumn::make('commentaire')
                    ->label('Commentaire')
                    ->limit(30)
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('est_travaille')
                    ->label('Est travaillé')
                    ->trueLabel('Oui')
                    ->falseLabel('Non')
                    ->placeholder('Tous'),
                SelectFilter::make('employeur_id')
                    ->label('Employé')
                    ->relationship('employeur', 'nom')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('plage_horaire_id')
                    ->label('Plage horaire')
                    ->relationship('plageHoraire', 'nom')
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->tooltip('Modifier ce planning'),
                Tables\Actions\DeleteAction::make()
                    ->tooltip('Supprimer ce planning'),
                TableAction::make('toggleTravaille')
                    ->label(function (Jour $record) {
                        return $record->est_travaille ? 'Marquer comme non travaillé' : 'Marquer comme travaillé';
                    })
                    ->icon(function (Jour $record) {
                        return $record->est_travaille ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle';
                    })
                    ->color(function (Jour $record) {
                        return $record->est_travaille ? 'danger' : 'success';
                    })
                    ->action(function (Jour $record) {
                        $record->update(['est_travaille' => !$record->est_travaille]);
                        
                        Notification::make()
                            ->title('Statut du jour mis à jour')
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('marquerTravaille')
                        ->label('Marquer comme travaillé')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(function (Collection $records) {
                            $count = 0;
                            foreach ($records as $record) {
                                if (!$record->est_travaille) {
                                    $record->update(['est_travaille' => true]);
                                    $count++;
                                }
                            }
                            
                            Notification::make()
                                ->title($count . ' plannings marqués comme travaillés')
                                ->success()
                                ->send();
                        }),
                    Tables\Actions\BulkAction::make('marquerNonTravaille')
                        ->label('Marquer comme non travaillé')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->action(function (Collection $records) {
                            $count = 0;
                            foreach ($records as $record) {
                                if ($record->est_travaille) {
                                    $record->update(['est_travaille' => false]);
                                    $count++;
                                }
                            }
                            
                            Notification::make()
                                ->title($count . ' plannings marqués comme non travaillés')
                                ->success()
                                ->send();
                        }),
                ]),
            ])
            ->headerActions([
                TableAction::make('affectationMasse')
                    ->label('Affectation en masse')
                    ->icon('heroicon-o-user-group')
                    ->size(ActionSize::Large)
                    ->color('primary')
                    ->form([
                        Select::make('plage_horaire_id')
                            ->label('Plage horaire')
                            ->relationship('plageHoraire', 'nom')
                            ->getOptionLabelFromRecordUsing(fn ($record) => $record->nom . ' (' . ($record->getPlageFormatee() ?? 'Horaire non défini') . ')')
                            ->searchable()
                            ->preload()
                            ->required(),
                        CheckboxList::make('employeur_ids')
                            ->label('Employés')
                            ->options(function () {
                                $user = auth()->user();
                                $entrepriseId = $user->entreprise_id;
                                
                                // Si l'utilisateur est SuperAdmin ou Support, il peut voir tous les employés
                                $query = Employeur::query();
                                if (!$user->isSuperAdmin() && !$user->isSupport()) {
                                    $query->where('entreprise_id', $entrepriseId);
                                }
                                
                                return $query->pluck('nom', 'id');
                            })
                            ->searchable()
                            ->bulkToggleable()
                            ->gridDirection('row')
                            ->columns(2)
                            ->required(),
                        Toggle::make('est_travaille')
                            ->label('Est travaillé')
                            ->default(true),
                        Textarea::make('commentaire')
                            ->label('Commentaire')
                            ->maxLength(1000),
                    ])
                    ->action(function (array $data) {
                        $user = auth()->user();
                        $entrepriseId = $user->entreprise_id;
                        
                        // Récupérer la plage horaire
                        $plageHoraireId = $data['plage_horaire_id'];
                        $employeurIds = $data['employeur_ids'];
                        $estTravaille = $data['est_travaille'] ?? true;
                        $commentaire = $data['commentaire'] ?? null;
                        
                        $count = 0;
                        $updated = 0;
                        
                        // Créer un jour pour chaque employé sélectionné
                        foreach ($employeurIds as $employeurId) {
                            // Vérifier si un jour existe déjà pour cet employé et cette plage horaire
                            $jourExistant = Jour::where('employeur_id', $employeurId)
                                ->where('plage_horaire_id', $plageHoraireId)
                                ->first();
                                
                            if (!$jourExistant) {
                                // S'assurer que l'entreprise_id est correctement défini
                                $employeurEntrepriseId = $entrepriseId;
                                
                                // Si l'utilisateur est SuperAdmin ou Support, récupérer l'entreprise_id de l'employé
                                if ($user->isSuperAdmin() || $user->isSupport()) {
                                    $employeur = Employeur::find($employeurId);
                                    if ($employeur) {
                                        $employeurEntrepriseId = $employeur->entreprise_id;
                                    }
                                }
                                
                                Jour::create([
                                    'employeur_id' => $employeurId,
                                    'plage_horaire_id' => $plageHoraireId,
                                    'entreprise_id' => $employeurEntrepriseId,
                                    'est_travaille' => $estTravaille,
                                    'commentaire' => $commentaire,
                                ]);
                                $count++;
                            } else {
                                // Mettre à jour le jour existant
                                $jourExistant->update([
                                    'est_travaille' => $estTravaille,
                                    'commentaire' => $commentaire,
                                ]);
                                $updated++;
                            }
                        }
                        
                        $message = '';
                        if ($count > 0) {
                            $message .= $count . ' plannings journaliers créés';
                        }
                        
                        if ($updated > 0) {
                            if ($message) {
                                $message .= ' et ';
                            }
                            $message .= $updated . ' plannings journaliers mis à jour';
                        }
                        
                        Notification::make()
                            ->title($message)
                            ->success()
                            ->send();
                    })
            ])
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('Aucun planning journalier')
            ->emptyStateDescription('Créez votre premier planning journalier en cliquant sur le bouton ci-dessous.')
            ->emptyStateIcon('heroicon-o-calendar');
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\PresencesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListJours::route('/'),
            'create' => Pages\CreateJour::route('/create'),
            'edit' => Pages\EditJour::route('/{record}/edit'),
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
