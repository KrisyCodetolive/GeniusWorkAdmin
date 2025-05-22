<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AppareilBiometriqueResource\Pages;
use App\Filament\Resources\AppareilBiometriqueResource\RelationManagers;
use App\Models\AppareilBiometrique;
use App\Traits\HasEntrepriseScope;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use App\Services\Biometrique\AppareilBiometriqueService;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\Action;
use Illuminate\Support\Collection;

class AppareilBiometriqueResource extends Resource
{
    use HasEntrepriseScope;
    
    protected static ?string $model = AppareilBiometrique::class;

    protected static ?string $navigationIcon = 'heroicon-o-finger-print';
    protected static ?string $navigationGroup = 'Configuration';
    protected static ?int $navigationSort = 4;

    public static function getNavigationLabel(): string
    {
        return __('Appareils Biométriques');
    }

    public static function getNavigationBadge(): ?string
    {
        return AppareilBiometrique::count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'success';
    }
    
    public static function canAccess(): bool
    {
        $user = auth()->user();
        return $user->isSuperAdmin() || $user->isSupport();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Le formulaire est défini dans CreateAppareilBiometrique.php et EditAppareilBiometrique.php
            ]);
    }

    public static function table(Table $table): Table
    {
        $user = auth()->user();
        $isSuperAdminOrSupport = $user->isSuperAdmin() || $user->isSupport();
        
        return $table
            ->columns([
                TextColumn::make('nom')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('site.nom')
                    ->label('Site')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('modele')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('fabricant')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('adresse_ip')
                    ->label('Adresse IP')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('port')
                    ->sortable(),
                TextColumn::make('protocole')
                    ->sortable(),
                ToggleColumn::make('sync_auto_enabled')
                    ->label('Sync Auto')
                    ->sortable(),
                TextColumn::make('dernier_sync')
                    ->label('Dernière Sync')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('statut')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'actif' => 'success',
                        'inactif' => 'danger',
                        'maintenance' => 'warning',
                        'erreur' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('entreprise_id')
                    ->label('Entreprise')
                    ->relationship('entreprise', 'nom')
                    ->searchable()
                    ->preload()
                    ->visible($isSuperAdminOrSupport),
                SelectFilter::make('site_id')
                    ->label('Site')
                    ->relationship('site', 'nom')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('statut')
                    ->options([
                        'actif' => 'Actif',
                        'inactif' => 'Inactif',
                        'maintenance' => 'Maintenance',
                        'erreur' => 'Erreur',
                    ]),
                SelectFilter::make('fabricant')
                    ->options(function () {
                        return AppareilBiometrique::distinct()->pluck('fabricant', 'fabricant')->toArray();
                    }),
                TernaryFilter::make('sync_auto_enabled')
                    ->label('Synchronisation Auto')
                    ->queries(
                        true: fn (Builder $query) => $query->where('sync_auto_enabled', true),
                        false: fn (Builder $query) => $query->where('sync_auto_enabled', false),
                    ),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Action::make('tester_connexion')
                    ->label('Tester Connexion')
                    ->icon('heroicon-o-signal')
                    ->color('success')
                    ->action(function (AppareilBiometrique $record, AppareilBiometriqueService $appareilService) {
                        $success = $appareilService->testerConnexion($record);
                        
                        if ($success) {
                            Notification::make()
                                ->title('Connexion réussie')
                                ->success()
                                ->send();
                        } else {
                            Notification::make()
                                ->title('Échec de connexion')
                                ->danger()
                                ->send();
                        }
                    }),
                Action::make('synchroniser')
                    ->label('Synchroniser')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->action(function (AppareilBiometrique $record, AppareilBiometriqueService $appareilService) {
                        try {
                            $appareilService->synchroniserDonnees($record);
                            
                            Notification::make()
                                ->title('Synchronisation réussie')
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Échec de synchronisation')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    })
                    ->visible(fn (AppareilBiometrique $record): bool => $record->estActif()),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('synchroniser_bulk')
                        ->label('Synchroniser')
                        ->icon('heroicon-o-arrow-path')
                        ->action(function (AppareilBiometriqueService $appareilService, $records) {
                            $success = 0;
                            $failed = 0;
                            
                            foreach ($records as $record) {
                                if ($record->estActif()) {
                                    try {
                                        $appareilService->synchroniserLogs($record);
                                        $success++;
                                    } catch (\Exception $e) {
                                        $failed++;
                                    }
                                } else {
                                    $failed++;
                                }
                            }
                            
                            Notification::make()
                                ->title("Synchronisation terminée")
                                ->body("{$success} appareils synchronisés, {$failed} échecs")
                                ->success()
                                ->send();
                        }),
                    Tables\Actions\BulkAction::make('activer_bulk')
                        ->label('Activer')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(function (AppareilBiometriqueService $appareilService, $records) {
                            foreach ($records as $record) {
                                $record->update(['statut' => 'actif']);
                            }
                            
                            Notification::make()
                                ->title('Appareils activés avec succès')
                                ->success()
                                ->send();
                        }),
                    Tables\Actions\BulkAction::make('desactiver_bulk')
                        ->label('Désactiver')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->action(function (AppareilBiometriqueService $appareilService, $records) {
                            foreach ($records as $record) {
                                $record->update(['statut' => 'inactif']);
                            }
                            
                            Notification::make()
                                ->title('Appareils désactivés avec succès')
                                ->success()
                                ->send();
                        }),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
    
    public static function getRelations(): array
    {
        return [
            RelationManagers\LogsRelationManager::class,
            RelationManagers\UtilisateursEnregistresRelationManager::class,
            RelationManagers\PointagesRelationManager::class,
        ];
    }
    
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAppareilBiometriques::route('/'),
            'create' => Pages\CreateAppareilBiometrique::route('/create'),
            'view' => Pages\ViewAppareilBiometrique::route('/{record}'),
            'edit' => Pages\EditAppareilBiometrique::route('/{record}/edit'),
        ];
    }
    
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        
        $user = auth()->user();
        
        // Si l'utilisateur n'est ni SuperAdmin ni Support, filtrer par entreprise
        if (!$user->isSuperAdmin() && !$user->isSupport()) {
            $query->where('entreprise_id', $user->entreprise_id);
        }
        
        return $query;
    }
    
    public static function mutateFormDataBeforeCreate(array $data): array
    {
        $user = auth()->user();
        
        // Si l'utilisateur n'est pas SuperAdmin ou Support, utiliser son entreprise_id
        if (!$user->isSuperAdmin() && !$user->isSupport()) {
            $data['entreprise_id'] = $user->entreprise_id;
        }
        
        return $data;
    }
}
