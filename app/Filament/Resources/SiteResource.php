<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SiteResource\Pages;
use App\Filament\Resources\SiteResource\RelationManagers;
use App\Filament\Resources\SiteResource\Widgets\SiteStatsWidget;
use App\Filament\Resources\SiteResource\Widgets\SiteGeographieWidget;
use App\Filament\Resources\SiteResource\Widgets\SitePointageWidget;
use App\Models\Site;
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
use Filament\Notifications\Notification;

class SiteResource extends Resource
{
    protected static ?string $model = Site::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';
    protected static ?string $navigationGroup = 'Structure Organisationnelle';
    protected static ?int $navigationSort = 3;

    public static function getNavigationLabel(): string
    {
        return __('Sites');
    }

    public static function getNavigationBadge(): ?string
    {
        return Site::count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'success';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Le formulaire est défini dans CreateSite.php et EditSite.php
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nom')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('entreprise.nom')
                    ->label('Entreprise')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('ville')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('pays')
                    ->searchable()
                    ->sortable(),
                ToggleColumn::make('has_geofencing')
                    ->label('Geofencing')
                    ->disabled(),
                TextColumn::make('statut')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'actif' => 'success',
                        'inactif' => 'danger',
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
                    ->preload(),
                SelectFilter::make('statut')
                    ->options([
                        'actif' => 'Actif',
                        'inactif' => 'Inactif',
                    ]),
                TernaryFilter::make('has_geofencing')
                    ->label('Geofencing')
                    ->queries(
                        true: fn (Builder $query) => $query->where('has_geofencing', true),
                        false: fn (Builder $query) => $query->where('has_geofencing', false),
                    ),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\Action::make('kiosk')
                    ->label('Mode Kiosque')
                    ->icon('heroicon-o-tv')
                    ->color('warning')
                    ->url(fn (Site $record): string => route('kiosk.index', $record->kiosk_token))
                    ->openUrlInNewTab()
                    ->visible(fn (Site $record): bool => !empty($record->kiosk_token)),
                Tables\Actions\Action::make('copyKioskUrl')
                    ->label('Copier URL Kiosque')
                    ->icon('heroicon-o-clipboard')
                    ->action(function (Site $record) {
                        $url = route('kiosk.index', $record->kiosk_token);
                        Notification::make()
                            ->title('URL Kiosque')
                            ->body($url)
                            ->success()
                            ->persistent()
                            ->send();
                    })
                    ->visible(fn (Site $record): bool => !empty($record->kiosk_token)),
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
                    ->icon('heroicon-o-building-office-2')
                    ->visible(fn () => auth()->user()->entreprise_id && !auth()->user()->isSuperAdmin() && !auth()->user()->isSupport())
                    ->action(function () {
                        $user = auth()->user();
                        $entreprise = $user->entreprise;
                        
                        if (!$entreprise) {
                            Filament\Notifications\Notification::make()
                                ->title('Erreur')
                                ->body('Vous devez être associé à une entreprise pour générer des exemples de sites.')
                                ->danger()
                                ->send();
                            return;
                        }
                        
                        // Générer les exemples de sites
                        $result = \Database\Seeders\SiteExempleSeeder::createForEntreprise($entreprise);
                        
                        // Notification de succès
                        Filament\Notifications\Notification::make()
                            ->title('Exemples générés')
                            ->body(count($result['created']) . ' sites exemples ont été créés pour votre entreprise.' . 
                                   ($result['existants'] > 0 ? ' ' . $result['existants'] . ' sites existaient déjà.' : ''))
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Générer des exemples de sites')
                    ->modalDescription('Cette action va créer des sites exemples pour votre entreprise. Vous pourrez les modifier ou les supprimer par la suite.')
                    ->modalSubmitActionLabel('Générer'),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\EmployesRelationManager::class,
            RelationManagers\PointagesRelationManager::class,
            RelationManagers\VisitesRelationManager::class,
        ];
    }

    public static function getWidgets(): array
    {
        return [
            SiteStatsWidget::class,
            SiteGeographieWidget::class,
            SitePointageWidget::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSites::route('/'),
            'create' => Pages\CreateSite::route('/create'),
            'edit' => Pages\EditSite::route('/{record}/edit'),
            'view' => Pages\ViewSite::route('/{record}'),
            'map' => Pages\MapSites::route('/map'),
        ];
    }
}
