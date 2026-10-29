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

    /**
     * Vidéo diffusée sur l'écran du kiosque : envoi d'une nouvelle vidéo, ou retour à la vidéo par défaut.
     * Le kiosque la récupère seul (rechargement périodique), sans toucher au mini PC.
     *
     * @param  class-string  $classe  Tables\Actions\Action ou Filament\Actions\Action
     * @return array<int, object>
     */
    public static function actionsVideoKiosque(string $classe): array
    {
        return [
            $classe::make('videoKiosque')
                ->label('Vidéo du kiosque')
                ->icon('heroicon-o-film')
                ->color('info')
                ->visible(fn (Site $record): bool => auth()->user()->can('update', $record))
                ->modalHeading(fn (Site $record) => "Vidéo du kiosque — {$record->nom}")
                ->modalDescription(fn (Site $record) => $record->kiosk_video
                    ? 'Une vidéo personnalisée est diffusée. En envoyer une nouvelle la remplace.'
                    : 'La vidéo par défaut est diffusée. Envoyez-en une pour la remplacer.')
                ->modalSubmitActionLabel('Mettre en ligne')
                ->form([
                    Forms\Components\FileUpload::make('kiosk_video')
                        ->label('Nouvelle vidéo')
                        ->helperText('MP4 ou WebM, 500 Mo maximum. Au-delà de 100 Mo, l\'envoi peut être refusé par le proxy (Cloudflare) : compressez la vidéo (1080p, ~1 Mbit/s).')
                        ->disk(Site::DISQUE_VIDEO_KIOSQUE)
                        ->directory('kiosk/videos')
                        ->visibility('public')
                        ->acceptedFileTypes(['video/mp4', 'video/webm'])
                        ->maxSize(512000)
                        ->required(),
                ])
                ->action(function (Site $record, array $data) {
                    $record->update(['kiosk_video' => $data['kiosk_video']]);

                    Notification::make()
                        ->success()
                        ->title('Vidéo mise en ligne')
                        ->body('Le kiosque l\'affichera dans la minute qui suit.')
                        ->send();
                }),

            $classe::make('videoKiosqueParDefaut')
                ->label('Vidéo par défaut')
                ->icon('heroicon-o-arrow-uturn-left')
                ->color('gray')
                ->visible(fn (Site $record): bool => filled($record->kiosk_video) && auth()->user()->can('update', $record))
                ->requiresConfirmation()
                ->modalHeading(fn (Site $record) => "Revenir à la vidéo par défaut — {$record->nom}")
                ->modalDescription('La vidéo personnalisée sera supprimée du serveur.')
                ->action(function (Site $record) {
                    $record->update(['kiosk_video' => null]);

                    Notification::make()->success()->title('Vidéo par défaut rétablie')->send();
                }),
        ];
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
                ...static::actionsVideoKiosque(Tables\Actions\Action::class),
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
