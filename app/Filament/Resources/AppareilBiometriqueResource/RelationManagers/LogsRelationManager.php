<?php

namespace App\Filament\Resources\AppareilBiometriqueResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Models\LogAppareilBiometrique;
use App\Services\Biometrique\LogAppareilBiometriqueService;
use Filament\Notifications\Notification;

class LogsRelationManager extends RelationManager
{
    protected static string $relationship = 'logs';

    protected static ?string $recordTitleAttribute = 'type_evenement';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('type_evenement')
                    ->label('Type d\'événement')
                    ->options([
                        'connexion' => 'Connexion',
                        'deconnexion' => 'Déconnexion',
                        'erreur' => 'Erreur',
                        'synchronisation' => 'Synchronisation',
                        'pointage' => 'Pointage',
                        'configuration' => 'Configuration',
                        'creation' => 'Création',
                        'modification' => 'Modification',
                        'suppression' => 'Suppression',
                    ])
                    ->required(),
                Forms\Components\Select::make('statut')
                    ->label('Statut')
                    ->options([
                        'success' => 'Succès',
                        'error' => 'Erreur',
                        'warning' => 'Avertissement',
                        'info' => 'Information',
                    ])
                    ->required(),
                Forms\Components\DateTimePicker::make('date_evenement')
                    ->label('Date de l\'événement')
                    ->required(),
                Forms\Components\KeyValue::make('details')
                    ->label('Détails')
                    ->keyLabel('Clé')
                    ->valueLabel('Valeur')
                    ->addable(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('type_evenement')
            ->columns([
                Tables\Columns\TextColumn::make('type_evenement')
                    ->label('Type d\'événement')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('statut')
                    ->label('Statut')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'success' => 'success',
                        'error' => 'danger',
                        'warning' => 'warning',
                        'info' => 'info',
                        default => 'gray',
                    })
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('date_evenement')
                    ->label('Date')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Utilisateur')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('details')
                    ->label('Message')
                    ->formatStateUsing(fn ($state) => $state['message'] ?? '-')
                    ->searchable()
                    ->limit(50),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type_evenement')
                    ->label('Type d\'événement')
                    ->options([
                        'connexion' => 'Connexion',
                        'deconnexion' => 'Déconnexion',
                        'erreur' => 'Erreur',
                        'synchronisation' => 'Synchronisation',
                        'pointage' => 'Pointage',
                        'configuration' => 'Configuration',
                        'creation' => 'Création',
                        'modification' => 'Modification',
                        'suppression' => 'Suppression',
                    ]),
                Tables\Filters\SelectFilter::make('statut')
                    ->label('Statut')
                    ->options([
                        'success' => 'Succès',
                        'error' => 'Erreur',
                        'warning' => 'Avertissement',
                        'info' => 'Information',
                    ]),
                Tables\Filters\Filter::make('date_evenement')
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
                                fn (Builder $query, $date): Builder => $query->whereDate('date_evenement', '>=', $date),
                            )
                            ->when(
                                $data['date_to'],
                                fn (Builder $query, $date): Builder => $query->whereDate('date_evenement', '<=', $date),
                            );
                    }),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
                Tables\Actions\Action::make('purger_logs')
                    ->label('Purger les logs')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->form([
                        Forms\Components\TextInput::make('jours')
                            ->label('Purger les logs plus anciens que (jours)')
                            ->numeric()
                            ->default(90)
                            ->required(),
                    ])
                    ->action(function (array $data, RelationManager $livewire) {
                        $appareil = $livewire->getOwnerRecord();
                        $logService = app(LogAppareilBiometriqueService::class);
                        
                        $count = $logService->purgerLogsAnciens($appareil, $data['jours']);
                        
                        Notification::make()
                            ->title("{$count} logs ont été purgés")
                            ->success()
                            ->send();
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('date_evenement', 'desc');
    }
}
