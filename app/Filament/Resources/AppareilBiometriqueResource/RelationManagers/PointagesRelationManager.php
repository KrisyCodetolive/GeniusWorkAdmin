<?php

namespace App\Filament\Resources\AppareilBiometriqueResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Services\Biometrique\PointageBiometriqueService;
use Filament\Notifications\Notification;
use Carbon\Carbon;

class PointagesRelationManager extends RelationManager
{
    protected static string $relationship = 'pointages';

    protected static ?string $recordTitleAttribute = 'id';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('employeur_id')
                    ->label('Employé')
                    ->relationship('employeur', 'nom')
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\Select::make('type')
                    ->label('Type de pointage')
                    ->options([
                        'entree' => 'Entrée',
                        'sortie' => 'Sortie',
                        'pause_debut' => 'Début de pause',
                        'pause_fin' => 'Fin de pause',
                    ])
                    ->required(),
                Forms\Components\DateTimePicker::make('date_heure')
                    ->label('Date et heure')
                    ->required(),
                Forms\Components\TextInput::make('latitude')
                    ->label('Latitude')
                    ->numeric()
                    ->disabled(),
                Forms\Components\TextInput::make('longitude')
                    ->label('Longitude')
                    ->numeric()
                    ->disabled(),
                Forms\Components\TextInput::make('precision_geo')
                    ->label('Précision (m)')
                    ->numeric()
                    ->disabled(),
                Forms\Components\TextInput::make('distance_site')
                    ->label('Distance du site (m)')
                    ->numeric()
                    ->disabled(),
                Forms\Components\Select::make('statut')
                    ->label('Statut')
                    ->options([
                        'valide' => 'Valide',
                        'en_attente' => 'En attente',
                        'rejete' => 'Rejeté',
                    ])
                    ->required(),
                Forms\Components\Textarea::make('commentaire')
                    ->label('Commentaire')
                    ->maxLength(500),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                Tables\Columns\TextColumn::make('employeur.nom')
                    ->label('Employé')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'entree' => 'success',
                        'sortie' => 'danger',
                        'pause_debut' => 'warning',
                        'pause_fin' => 'info',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'entree' => 'Entrée',
                        'sortie' => 'Sortie',
                        'pause_debut' => 'Début pause',
                        'pause_fin' => 'Fin pause',
                        default => $state,
                    })
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('date_heure')
                    ->label('Date et heure')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('site.nom')
                    ->label('Site')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('distance_site')
                    ->label('Distance (m)')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('source')
                    ->label('Source')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'biometrique' => 'Biométrique',
                        default => $state,
                    })
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('statut')
                    ->label('Statut')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'valide' => 'success',
                        'en_attente' => 'warning',
                        'rejete' => 'danger',
                        default => 'gray',
                    })
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('commentaire')
                    ->label('Commentaire')
                    ->limit(30)
                    ->toggleable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('employeur_id')
                    ->label('Employé')
                    ->relationship('employeur', 'nom')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('type')
                    ->label('Type')
                    ->options([
                        'entree' => 'Entrée',
                        'sortie' => 'Sortie',
                        'pause_debut' => 'Début de pause',
                        'pause_fin' => 'Fin de pause',
                    ]),
                Tables\Filters\SelectFilter::make('statut')
                    ->label('Statut')
                    ->options([
                        'valide' => 'Valide',
                        'en_attente' => 'En attente',
                        'rejete' => 'Rejeté',
                    ]),
                Tables\Filters\Filter::make('date_heure')
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
                                fn (Builder $query, $date): Builder => $query->whereDate('date_heure', '>=', $date),
                            )
                            ->when(
                                $data['date_to'],
                                fn (Builder $query, $date): Builder => $query->whereDate('date_heure', '<=', $date),
                            );
                    }),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->using(function (array $data, RelationManager $livewire) {
                        $appareil = $livewire->getOwnerRecord();
                        $pointageService = app(PointageBiometriqueService::class);
                        
                        // Ajouter les informations de l'appareil
                        $data['source'] = 'biometrique';
                        $data['appareil_id'] = $appareil->id;
                        $data['site_id'] = $appareil->site_id;
                        $data['entreprise_id'] = $appareil->entreprise_id;
                        $data['methode_pointage_id'] = null; // À définir selon votre logique
                        
                        // Créer le pointage
                        return $pointageService->creerPointageManuel($data);
                    }),
                Tables\Actions\Action::make('synchroniser_pointages')
                    ->label('Synchroniser les pointages')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->action(function (RelationManager $livewire) {
                        $appareil = $livewire->getOwnerRecord();
                        $pointageService = app(PointageBiometriqueService::class);
                        
                        try {
                            $count = $pointageService->synchroniserPointages($appareil);
                            
                            Notification::make()
                                ->title("Synchronisation réussie")
                                ->body("{$count} pointages synchronisés")
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Échec de la synchronisation')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('valider')
                    ->label('Valider')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn ($record) => $record->statut !== 'valide')
                    ->action(function ($record) {
                        $pointageService = app(PointageBiometriqueService::class);
                        $pointageService->validerPointage($record);
                        
                        Notification::make()
                            ->title('Pointage validé')
                            ->success()
                            ->send();
                    }),
                Tables\Actions\Action::make('rejeter')
                    ->label('Rejeter')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn ($record) => $record->statut !== 'rejete')
                    ->form([
                        Forms\Components\Textarea::make('commentaire')
                            ->label('Motif du rejet')
                            ->required(),
                    ])
                    ->action(function ($record, array $data) {
                        $pointageService = app(PointageBiometriqueService::class);
                        $pointageService->rejeterPointage($record, $data['commentaire']);
                        
                        Notification::make()
                            ->title('Pointage rejeté')
                            ->success()
                            ->send();
                    }),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('valider_bulk')
                        ->label('Valider')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(function ($records) {
                            $pointageService = app(PointageBiometriqueService::class);
                            
                            foreach ($records as $record) {
                                if ($record->statut !== 'valide') {
                                    $pointageService->validerPointage($record);
                                }
                            }
                            
                            Notification::make()
                                ->title('Pointages validés avec succès')
                                ->success()
                                ->send();
                        }),
                    Tables\Actions\BulkAction::make('rejeter_bulk')
                        ->label('Rejeter')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->form([
                            Forms\Components\Textarea::make('commentaire')
                                ->label('Motif du rejet')
                                ->required(),
                        ])
                        ->action(function ($records, array $data) {
                            $pointageService = app(PointageBiometriqueService::class);
                            
                            foreach ($records as $record) {
                                if ($record->statut !== 'rejete') {
                                    $pointageService->rejeterPointage($record, $data['commentaire']);
                                }
                            }
                            
                            Notification::make()
                                ->title('Pointages rejetés avec succès')
                                ->success()
                                ->send();
                        }),
                ]),
            ])
            ->defaultSort('date_heure', 'desc');
    }
}
