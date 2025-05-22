<?php

namespace App\Filament\Resources\AppareilBiometriqueResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Services\Biometrique\AppareilBiometriqueService;
use Filament\Notifications\Notification;
use Carbon\Carbon;

class UtilisateursEnregistresRelationManager extends RelationManager
{
    protected static string $relationship = 'utilisateursEnregistres';

    protected static ?string $recordTitleAttribute = 'name';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->label('Utilisateur')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\TextInput::make('identifiant_biometrique')
                    ->label('Identifiant biométrique')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('type_donnee')
                    ->label('Type de donnée')
                    ->options([
                        'empreinte' => 'Empreinte',
                        'visage' => 'Visage',
                        'carte' => 'Carte',
                        'code' => 'Code',
                        'multiple' => 'Multiple',
                    ])
                    ->required(),
                Forms\Components\DateTimePicker::make('date_enregistrement')
                    ->label('Date d\'enregistrement')
                    ->default(now())
                    ->required(),
                Forms\Components\Select::make('statut')
                    ->label('Statut')
                    ->options([
                        'actif' => 'Actif',
                        'inactif' => 'Inactif',
                    ])
                    ->default('actif')
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Nom')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.email')
                    ->label('Email')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('identifiant_biometrique')
                    ->label('ID Biométrique')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('type_donnee')
                    ->label('Type de donnée')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'empreinte' => 'success',
                        'visage' => 'warning',
                        'carte' => 'info',
                        'code' => 'gray',
                        'multiple' => 'primary',
                        default => 'gray',
                    })
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('date_enregistrement')
                    ->label('Date d\'enregistrement')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('statut')
                    ->label('Statut')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'actif' => 'success',
                        'inactif' => 'danger',
                        default => 'gray',
                    })
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type_donnee')
                    ->label('Type de donnée')
                    ->options([
                        'empreinte' => 'Empreinte',
                        'visage' => 'Visage',
                        'carte' => 'Carte',
                        'code' => 'Code',
                        'multiple' => 'Multiple',
                    ]),
                Tables\Filters\SelectFilter::make('statut')
                    ->label('Statut')
                    ->options([
                        'actif' => 'Actif',
                        'inactif' => 'Inactif',
                    ]),
                Tables\Filters\Filter::make('date_enregistrement')
                    ->form([
                        Forms\Components\DatePicker::make('date_from')
                            ->label('Date de début'),
                        Forms\Components\DatePicker::make('date_to')
                            ->label('Date de fin'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['date_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('date_enregistrement', '>=', $date),
                            )
                            ->when(
                                $data['date_to'],
                                fn (Builder $query, $date): Builder => $query->whereDate('date_enregistrement', '<=', $date),
                            );
                    }),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->after(function ($record, RelationManager $livewire) {
                        $appareil = $livewire->getOwnerRecord();
                        $appareilService = app(AppareilBiometriqueService::class);
                        
                        try {
                            // Enregistrer l'utilisateur sur l'appareil biométrique
                            $appareilService->enregistrerUtilisateur($appareil, $record->user, $record->pivot->identifiant_biometrique, $record->pivot->type_donnee);
                            
                            Notification::make()
                                ->title('Utilisateur enregistré avec succès')
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Erreur lors de l\'enregistrement sur l\'appareil')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
                Tables\Actions\AttachAction::make()
                    ->preloadRecordSelect()
                    ->form(fn (Tables\Actions\AttachAction $action): array => [
                        $action->getRecordSelect(),
                        Forms\Components\TextInput::make('identifiant_biometrique')
                            ->label('Identifiant biométrique')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Select::make('type_donnee')
                            ->label('Type de donnée')
                            ->options([
                                'empreinte' => 'Empreinte',
                                'visage' => 'Visage',
                                'carte' => 'Carte',
                                'code' => 'Code',
                                'multiple' => 'Multiple',
                            ])
                            ->default('empreinte')
                            ->required(),
                        Forms\Components\DateTimePicker::make('date_enregistrement')
                            ->label('Date d\'enregistrement')
                            ->default(now())
                            ->required(),
                        Forms\Components\Select::make('statut')
                            ->label('Statut')
                            ->options([
                                'actif' => 'Actif',
                                'inactif' => 'Inactif',
                            ])
                            ->default('actif')
                            ->required(),
                    ]),
                Tables\Actions\Action::make('synchroniser_utilisateurs')
                    ->label('Synchroniser les utilisateurs')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->action(function (RelationManager $livewire) {
                        $appareil = $livewire->getOwnerRecord();
                        $appareilService = app(AppareilBiometriqueService::class);
                        
                        try {
                            $appareilService->synchroniserUtilisateurs($appareil);
                            
                            Notification::make()
                                ->title('Synchronisation des utilisateurs réussie')
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Échec de la synchronisation des utilisateurs')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->using(function ($record, array $data, RelationManager $livewire) {
                        $appareil = $livewire->getOwnerRecord();
                        $appareilService = app(AppareilBiometriqueService::class);
                        
                        // Mettre à jour les données dans la table pivot
                        $record->utilisateursEnregistres()->updateExistingPivot($appareil->id, [
                            'identifiant_biometrique' => $data['identifiant_biometrique'],
                            'type_donnee' => $data['type_donnee'],
                            'date_enregistrement' => $data['date_enregistrement'],
                            'statut' => $data['statut'],
                        ]);
                        
                        try {
                            // Mettre à jour l'utilisateur sur l'appareil biométrique
                            $appareilService->mettreAJourUtilisateur($appareil, $record, $data['identifiant_biometrique'], $data['type_donnee'], $data['statut']);
                            
                            Notification::make()
                                ->title('Utilisateur mis à jour avec succès')
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Erreur lors de la mise à jour sur l\'appareil')
                                ->body($e->getMessage())
                                ->warning()
                                ->send();
                        }
                        
                        return $record;
                    }),
                Tables\Actions\DetachAction::make()
                    ->after(function ($record, RelationManager $livewire) {
                        $appareil = $livewire->getOwnerRecord();
                        $appareilService = app(AppareilBiometriqueService::class);
                        
                        try {
                            // Supprimer l'utilisateur de l'appareil biométrique
                            $appareilService->supprimerUtilisateur($appareil, $record);
                            
                            Notification::make()
                                ->title('Utilisateur supprimé avec succès')
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Erreur lors de la suppression sur l\'appareil')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DetachBulkAction::make(),
                    Tables\Actions\BulkAction::make('activer')
                        ->label('Activer')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(function ($records, RelationManager $livewire) {
                            $appareil = $livewire->getOwnerRecord();
                            $appareilService = app(AppareilBiometriqueService::class);
                            
                            foreach ($records as $record) {
                                $record->utilisateursEnregistres()->updateExistingPivot($appareil->id, [
                                    'statut' => 'actif',
                                ]);
                                
                                try {
                                    $appareilService->mettreAJourStatutUtilisateur($appareil, $record, 'actif');
                                } catch (\Exception $e) {
                                    // Continuer malgré les erreurs
                                }
                            }
                            
                            Notification::make()
                                ->title('Utilisateurs activés avec succès')
                                ->success()
                                ->send();
                        }),
                    Tables\Actions\BulkAction::make('desactiver')
                        ->label('Désactiver')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->action(function ($records, RelationManager $livewire) {
                            $appareil = $livewire->getOwnerRecord();
                            $appareilService = app(AppareilBiometriqueService::class);
                            
                            foreach ($records as $record) {
                                $record->utilisateursEnregistres()->updateExistingPivot($appareil->id, [
                                    'statut' => 'inactif',
                                ]);
                                
                                try {
                                    $appareilService->mettreAJourStatutUtilisateur($appareil, $record, 'inactif');
                                } catch (\Exception $e) {
                                    // Continuer malgré les erreurs
                                }
                            }
                            
                            Notification::make()
                                ->title('Utilisateurs désactivés avec succès')
                                ->success()
                                ->send();
                        }),
                ]),
            ])
            ->defaultSort('date_enregistrement', 'desc');
    }
}
