<?php

namespace App\Filament\Resources\EquipeResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Models\Employeur;
use Carbon\Carbon;

class MembresRelationManager extends RelationManager
{
    protected static string $relationship = 'membres';

    protected static ?string $recordTitleAttribute = 'nom';

    protected static ?string $title = 'Membres de l\'équipe';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('employeur_id')
                    ->label('Employé')
                    ->options(function () {
                        $entrepriseId = $this->ownerRecord->entreprise_id;
                        return Employeur::where('entreprise_id', $entrepriseId)
                            ->pluck('nom', 'id');
                    })
                    ->searchable()
                    ->required(),
                Forms\Components\Toggle::make('est_actif')
                    ->label('Est actif')
                    ->default(true),
                Forms\Components\DatePicker::make('date_debut')
                    ->label('Date de début')
                    ->default(Carbon::today()),
                Forms\Components\DatePicker::make('date_fin')
                    ->label('Date de fin')
                    ->after('date_debut'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('nom')
            ->columns([
                Tables\Columns\TextColumn::make('nom')
                    ->label('Nom')
                    ->formatStateUsing(function ($record) {
                        return "{$record->prenom} {$record->nom}" . ($record->matricule ? " ({$record->matricule})" : '');
                    })
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\IconColumn::make('pivot.est_actif')
                    ->label('Actif')
                    ->boolean(),
                Tables\Columns\TextColumn::make('pivot.date_debut')
                    ->label('Date de début')
                    ->date(),
                Tables\Columns\TextColumn::make('pivot.date_fin')
                    ->label('Date de fin')
                    ->date(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('pivot.est_actif')
                    ->label('Actif'),
            ])
            ->headerActions([
                Tables\Actions\AttachAction::make()
                    ->label('Ajouter des membres')
                    ->preloadRecordSelect()
                    ->form(fn (Tables\Actions\AttachAction $action): array => [
                        $action->getRecordSelect()
                            ->label('Employés')
                            ->multiple()
                            ->options(function () {
                                $entrepriseId = $this->ownerRecord->entreprise_id;
                                // Ne proposer que les employés qui ne sont pas déjà dans une équipe active
                                $employeursDejaEnEquipe = \App\Models\Equipe::where('entreprise_id', $entrepriseId)
                                    ->whereNot('id', $this->ownerRecord->id)
                                    ->with(['membres' => function($query) {
                                        $query->where('equipe_employeur.est_actif', true);
                                    }])
                                    ->get()
                                    ->pluck('membres')
                                    ->flatten()
                                    ->pluck('id')
                                    ->toArray();
                                
                                return Employeur::where('entreprise_id', $entrepriseId)
                                    ->whereNotIn('id', $employeursDejaEnEquipe)
                                    ->pluck('nom', 'id');
                            })
                            ->searchable()
                            ->required(),
                        Forms\Components\Toggle::make('est_actif')
                            ->label('Est actif')
                            ->default(true),
                        Forms\Components\DatePicker::make('date_debut')
                            ->label('Date de début')
                            ->default(Carbon::today()),
                        Forms\Components\DatePicker::make('date_fin')
                            ->label('Date de fin'),
                    ])
                    ->using(function (\App\Models\Employeur $employeur, array $data) {
                        // Vérifier si l'employé est déjà dans une autre équipe active
                        $estDejaEnEquipe = $employeur->equipes()
                            ->where('equipe_id', '!=', $this->ownerRecord->id)
                            ->where('equipe_employeur.est_actif', true)
                            ->exists();
                        
                        if ($estDejaEnEquipe && ($data['est_actif'] ?? true)) {
                            // Afficher une notification d'erreur
                            \Filament\Notifications\Notification::make()
                                ->title('Erreur')
                                ->body("L'employé {$employeur->prenom} {$employeur->nom} est déjà membre d'une autre équipe active.")
                                ->danger()
                                ->send();
                                
                            return null; // Annuler l'attachement
                        }
                        
                        // Si tout est bon, procéder à l'attachement
                        return [
                            'est_actif' => $data['est_actif'] ?? true,
                            'date_debut' => $data['date_debut'] ?? Carbon::today(),
                            'date_fin' => $data['date_fin'] ?? null,
                        ];
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->using(function ($record, array $data): Employeur {
                        // Si on essaie d'activer l'employé, vérifier qu'il n'est pas déjà actif dans une autre équipe
                        if (($data['est_actif'] ?? false) && !$record->pivot->est_actif) {
                            $estDejaEnEquipe = $record->equipes()
                                ->where('equipe_id', '!=', $this->ownerRecord->id)
                                ->where('equipe_employeur.est_actif', true)
                                ->exists();
                            
                            if ($estDejaEnEquipe) {
                                // Afficher une notification d'erreur
                                \Filament\Notifications\Notification::make()
                                    ->title('Erreur')
                                    ->body("L'employé {$record->prenom} {$record->nom} est déjà membre d'une autre équipe active.")
                                    ->danger()
                                    ->send();
                                    
                                return $record; // Ne pas mettre à jour
                            }
                        }
                        
                        // Si tout est bon, procéder à la mise à jour
                        $record->pivot->update([
                            'est_actif' => $data['est_actif'] ?? true,
                            'date_debut' => $data['date_debut'],
                            'date_fin' => $data['date_fin'],
                        ]);
                        
                        return $record;
                    }),
                Tables\Actions\DetachAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DetachBulkAction::make(),
                    Tables\Actions\BulkAction::make('activer')
                        ->label('Activer')
                        ->icon('heroicon-o-check')
                        ->action(function (\Illuminate\Database\Eloquent\Collection $records) {
                            $employesNonActives = 0;
                            $employesDejaEnEquipe = 0;
                            
                            foreach ($records as $record) {
                                // Ne traiter que les employés non actifs dans cette équipe
                                if (!$record->pivot->est_actif) {
                                    // Vérifier si l'employé est déjà dans une autre équipe active
                                    $estDejaEnEquipe = $record->equipes()
                                        ->where('equipe_id', '!=', $this->ownerRecord->id)
                                        ->where('equipe_employeur.est_actif', true)
                                        ->exists();
                                    
                                    if ($estDejaEnEquipe) {
                                        $employesDejaEnEquipe++;
                                    } else {
                                        $record->pivot->update(['est_actif' => true]);
                                        $employesNonActives++;
                                    }
                                }
                            }
                            
                            // Afficher une notification avec le résultat
                            if ($employesNonActives > 0 || $employesDejaEnEquipe > 0) {
                                $message = "";
                                if ($employesNonActives > 0) {
                                    $message .= "{$employesNonActives} employé(s) activé(s) avec succès. ";
                                }
                                if ($employesDejaEnEquipe > 0) {
                                    $message .= "{$employesDejaEnEquipe} employé(s) n'ont pas pu être activés car ils sont déjà membres d'une autre équipe active.";
                                }
                                
                                \Filament\Notifications\Notification::make()
                                    ->title('Activation des membres')
                                    ->body($message)
                                    ->color($employesDejaEnEquipe > 0 ? 'warning' : 'success')
                                    ->send();
                            }
                        })
                        ->requiresConfirmation(),
                    Tables\Actions\BulkAction::make('desactiver')
                        ->label('Désactiver')
                        ->icon('heroicon-o-x-mark')
                        ->action(function (\Illuminate\Database\Eloquent\Collection $records) {
                            foreach ($records as $record) {
                                $record->pivot->update(['est_actif' => false]);
                            }
                        })
                        ->requiresConfirmation(),
                ]),
            ]);
    }
}
