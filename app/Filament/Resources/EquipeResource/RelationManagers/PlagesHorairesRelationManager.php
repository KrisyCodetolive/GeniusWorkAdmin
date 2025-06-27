<?php

namespace App\Filament\Resources\EquipeResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Models\PlageHoraire;
use Carbon\Carbon;

class PlagesHorairesRelationManager extends RelationManager
{
    protected static string $relationship = 'plagesHoraires';

    protected static ?string $recordTitleAttribute = 'nom';

    protected static ?string $title = 'Plages horaires de l\'équipe';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('plage_horaire_id')
                    ->label('Plage horaire')
                    ->options(function () {
                        $entrepriseId = $this->ownerRecord->entreprise_id;
                        return PlageHoraire::where('entreprise_id', $entrepriseId)
                            ->pluck('nom', 'id');
                    })
                    ->searchable()
                    ->required(),
                Forms\Components\Toggle::make('est_actif')
                    ->label('Est actif')
                    ->default(true)
                    ->helperText('Une seule plage horaire peut être active à la fois. Activer celle-ci désactivera les autres.'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('nom')
            ->columns([
                Tables\Columns\TextColumn::make('nom')
                    ->label('Nom')
                    ->searchable()
                    ->sortable(),
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
                Tables\Columns\IconColumn::make('pivot.est_actif')
                    ->label('Actif')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('pivot.est_actif')
                    ->label('Actif'),
            ])
            ->headerActions([
                Tables\Actions\AttachAction::make()
                    ->label('Ajouter des plages horaires')
                    ->preloadRecordSelect()
                    ->form(fn (Tables\Actions\AttachAction $action): array => [
                        $action->getRecordSelect()
                            ->label('Plages horaires')
                            ->multiple()
                            ->options(function () {
                                $entrepriseId = $this->ownerRecord->entreprise_id;
                                return PlageHoraire::where('entreprise_id', $entrepriseId)
                                    ->pluck('nom', 'id');
                            })
                            ->searchable()
                            ->required(),
                        Forms\Components\Toggle::make('est_actif')
                            ->label('Est actif')
                            ->default(true)
                            ->helperText('Une seule plage horaire peut être active à la fois. Activer celle-ci désactivera les autres.'),
                    ]),
                Tables\Actions\Action::make('appliquerAuxMembres')
                    ->label('Appliquer aux membres')
                    ->icon('heroicon-o-user-group')
                    ->action(function () {
                        $this->ownerRecord->synchroniserHoraires();
                        
                        \Filament\Notifications\Notification::make()
                            ->title('Horaires appliqués')
                            ->body('Les plages horaires ont été appliquées à tous les membres de l\'équipe.')
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Appliquer les horaires aux membres')
                    ->modalDescription('Cette action va appliquer toutes les plages horaires de l\'équipe à tous ses membres actifs.')
                    ->modalSubmitActionLabel('Appliquer')
                    ->color('success'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->using(function ($record, array $data): PlageHoraire {
                        // Si la plage horaire est activée, désactiver toutes les autres
                        if ($data['est_actif']) {
                            $this->ownerRecord->plagesHoraires()
                                ->where('plage_horaire_id', '!=', $record->id)
                                ->each(function ($plageHoraire) {
                                    $plageHoraire->pivot->update(['est_actif' => false]);
                                });
                        }
                        
                        $record->pivot->update([
                            'est_actif' => $data['est_actif'] ?? true,
                        ]);
                        
                        return $record;
                    }),
                Tables\Actions\DetachAction::make(),
                Tables\Actions\Action::make('appliquerCettePlage')
                    ->label('Appliquer cette plage')
                    ->icon('heroicon-o-user-group')
                    ->action(function ($record) {
                        // Activer cette plage horaire et désactiver les autres
                        $this->ownerRecord->plagesHoraires()
                            ->where('plage_horaire_id', '!=', $record->id)
                            ->each(function ($plageHoraire) {
                                $plageHoraire->pivot->update(['est_actif' => false]);
                            });
                        
                        $record->pivot->update(['est_actif' => true]);
                        
                        // Appliquer la plage horaire aux membres
                        $this->ownerRecord->assignerPlageHoraireAuxMembres($record);
                        
                        \Filament\Notifications\Notification::make()
                            ->title('Plage horaire appliquée')
                            ->body('La plage horaire a été appliquée à tous les membres de l\'équipe.')
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Appliquer cette plage horaire')
                    ->modalDescription('Cette action va appliquer cette plage horaire à tous les membres actifs de l\'équipe.')
                    ->modalSubmitActionLabel('Appliquer')
                    ->color('success'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DetachBulkAction::make(),
                    Tables\Actions\BulkAction::make('activer')
                        ->label('Activer')
                        ->icon('heroicon-o-check')
                        ->action(function (\Illuminate\Database\Eloquent\Collection $records) {
                            foreach ($records as $record) {
                                $record->pivot->update(['est_actif' => true]);
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
                    Tables\Actions\BulkAction::make('appliquerPlages')
                        ->label('Appliquer aux membres')
                        ->icon('heroicon-o-user-group')
                        ->action(function (\Illuminate\Database\Eloquent\Collection $records) {
                            foreach ($records as $record) {
                                $this->ownerRecord->assignerPlageHoraireAuxMembres(
                                    $record,
                                    $record->pivot->jour_semaine
                                );
                            }
                            
                            \Filament\Notifications\Notification::make()
                                ->title('Plages horaires appliquées')
                                ->body('Les plages horaires sélectionnées ont été appliquées à tous les membres de l\'équipe.')
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation()
                        ->color('success'),
                ]),
            ]);
    }
}
