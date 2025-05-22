<?php

namespace App\Filament\Resources\SiteResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Models\Visiteur;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\DateTimePicker;
use App\Services\VisiteurService;
use App\Services\VisiteService;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Collection;

class VisitesRelationManager extends RelationManager
{
    protected static string $relationship = 'visites';

    protected static ?string $recordTitleAttribute = 'motif_visite';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Informations du visiteur')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('visiteur_id')
                                    ->label('Visiteur existant')
                                    ->options(function () {
                                        $entrepriseId = $this->ownerRecord->entreprise_id;
                                        return Visiteur::where('entreprise_id', $entrepriseId)
                                            ->get()
                                            ->mapWithKeys(function ($visiteur) {
                                                return [$visiteur->id => $visiteur->nom_complet . ' (' . $visiteur->telephone . ')'];
                                            });
                                    })
                                    ->searchable()
                                    ->preload()
                                    ->helperText('Si le visiteur existe déjà, sélectionnez-le ici. Sinon, remplissez les champs ci-dessous.')
                                    ->placeholder('Sélectionnez un visiteur existant ou créez-en un nouveau')
                                    ->hidden(fn ($record) => $record !== null),
                                Forms\Components\Placeholder::make('visiteur_info')
                                    ->label('Visiteur')
                                    ->content(function ($record) {
                                        if (!$record || !$record->visiteur) {
                                            return 'Aucun visiteur sélectionné';
                                        }
                                        $visiteur = $record->visiteur;
                                        return $visiteur->nom_complet . ' (' . $visiteur->telephone . ')';
                                    })
                                    ->visible(fn ($record) => $record !== null),
                            ]),
                    ]),
                Section::make('Nouveau visiteur')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('nouveau_visiteur.nom')
                                    ->label('Nom')
                                    ->required()
                                    ->hidden(fn (callable $get) => $get('visiteur_id') !== null),
                                TextInput::make('nouveau_visiteur.prenom')
                                    ->label('Prénom')
                                    ->required()
                                    ->hidden(fn (callable $get) => $get('visiteur_id') !== null),
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('nouveau_visiteur.telephone')
                                    ->label('Téléphone')
                                    ->tel()
                                    ->required()
                                    ->hidden(fn (callable $get) => $get('visiteur_id') !== null),
                                TextInput::make('nouveau_visiteur.email')
                                    ->label('Email')
                                    ->email()
                                    ->hidden(fn (callable $get) => $get('visiteur_id') !== null),
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('nouveau_visiteur.organisation')
                                    ->label('Organisation')
                                    ->hidden(fn (callable $get) => $get('visiteur_id') !== null),
                                TextInput::make('nouveau_visiteur.fonction')
                                    ->label('Fonction/Poste')
                                    ->hidden(fn (callable $get) => $get('visiteur_id') !== null),
                            ]),
                    ])
                    ->hidden(fn ($record) => $record !== null),
                Section::make('Détails de la visite')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('motif_visite')
                                    ->label('Motif de la visite')
                                    ->required(),
                                TextInput::make('personne_a_rencontrer')
                                    ->label('Personne à rencontrer'),
                            ]),
                        Grid::make(2)
                            ->schema([
                                DateTimePicker::make('date_arrivee')
                                    ->label('Date d\'arrivée')
                                    ->required()
                                    ->default(now()),
                                DateTimePicker::make('date_depart')
                                    ->label('Date de départ')
                                    ->nullable(),
                            ]),
                        TextInput::make('departement_a_visiter')
                            ->label('Département à visiter'),
                        Textarea::make('commentaires')
                            ->label('Commentaires')
                            ->rows(3),
                        Select::make('statut')
                            ->label('Statut')
                            ->options([
                                'en_cours' => 'En cours',
                                'terminee' => 'Terminée',
                                'annulee' => 'Annulée',
                            ])
                            ->default('en_cours')
                            ->required(),
                        TextInput::make('badge_visiteur')
                            ->label('Badge visiteur')
                            ->placeholder('Numéro de badge attribué'),
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('visiteur.nom_complet')
                    ->label('Visiteur')
                    ->searchable(['visiteurs.nom', 'visiteurs.prenom'])
                    ->sortable(),
                Tables\Columns\TextColumn::make('visiteur.telephone')
                    ->label('Téléphone')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('motif_visite')
                    ->label('Motif')
                    ->searchable()
                    ->limit(30),
                Tables\Columns\TextColumn::make('date_arrivee')
                    ->label('Arrivée')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('date_depart')
                    ->label('Départ')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->placeholder('En cours'),
                Tables\Columns\TextColumn::make('personne_a_rencontrer')
                    ->label('Personne à rencontrer')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('badge_visiteur')
                    ->label('Badge')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\BadgeColumn::make('statut')
                    ->label('Statut')
                    ->colors([
                        'primary' => 'en_cours',
                        'success' => 'terminee',
                        'danger' => 'annulee',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'en_cours' => 'En cours',
                        'terminee' => 'Terminée',
                        'annulee' => 'Annulée',
                        default => $state,
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('statut')
                    ->label('Statut')
                    ->options([
                        'en_cours' => 'En cours',
                        'terminee' => 'Terminée',
                        'annulee' => 'Annulée',
                    ]),
                Tables\Filters\Filter::make('date_arrivee')
                    ->form([
                        Forms\Components\DatePicker::make('date_from')
                            ->label('Du'),
                        Forms\Components\DatePicker::make('date_until')
                            ->label('Au'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['date_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('date_arrivee', '>=', $date),
                            )
                            ->when(
                                $data['date_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('date_arrivee', '<=', $date),
                            );
                    }),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        // Ajouter l'entreprise_id du site
                        $data['entreprise_id'] = $this->ownerRecord->entreprise_id;
                        
                        // Si un nouveau visiteur est créé
                        if (empty($data['visiteur_id']) && !empty($data['nouveau_visiteur'])) {
                            $nouveauVisiteur = $data['nouveau_visiteur'];
                            $nouveauVisiteur['entreprise_id'] = $this->ownerRecord->entreprise_id;
                            
                            $visiteurService = App::make(VisiteurService::class);
                            $visiteur = $visiteurService->createVisiteur($nouveauVisiteur);
                            $data['visiteur_id'] = $visiteur->id;
                        }
                        
                        // Supprimer les données du nouveau visiteur car elles ont été traitées
                        unset($data['nouveau_visiteur']);
                        
                        return $data;
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('terminer')
                    ->label('Terminer')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn ($record) => $record->statut === 'en_cours')
                    ->action(function ($record) {
                        $record->update([
                            'statut' => 'terminee',
                            'date_depart' => now(),
                        ]);
                    })
                    ->requiresConfirmation(),
                Tables\Actions\Action::make('annuler')
                    ->label('Annuler')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn ($record) => $record->statut === 'en_cours')
                    ->action(function ($record) {
                        $record->update([
                            'statut' => 'annulee',
                        ]);
                    })
                    ->requiresConfirmation(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('terminerMultiple')
                        ->label('Terminer')
                        ->icon('heroicon-o-check-circle')
                        ->action(function (Collection $records) {
                            foreach ($records as $record) {
                                if ($record->statut === 'en_cours') {
                                    $record->update([
                                        'statut' => 'terminee',
                                        'date_depart' => now(),
                                    ]);
                                }
                            }
                        })
                        ->requiresConfirmation(),
                    Tables\Actions\BulkAction::make('annulerMultiple')
                        ->label('Annuler')
                        ->icon('heroicon-o-x-circle')
                        ->action(function (Collection $records) {
                            foreach ($records as $record) {
                                if ($record->statut === 'en_cours') {
                                    $record->update([
                                        'statut' => 'annulee',
                                    ]);
                                }
                            }
                        })
                        ->requiresConfirmation(),
                ]),
            ]);
    }
}
