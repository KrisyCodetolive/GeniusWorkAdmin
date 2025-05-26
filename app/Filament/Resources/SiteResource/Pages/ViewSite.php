<?php

namespace App\Filament\Resources\SiteResource\Pages;

use App\Filament\Resources\SiteResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\KeyValueEntry;
use Filament\Infolists\Infolist;

class ViewSite extends ViewRecord
{
    protected static string $resource = SiteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
    
    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Informations générales')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('entreprise.nom')
                                    ->label('Entreprise'),
                                TextEntry::make('nom')
                                    ->label('Nom du site'),
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('statut')
                                    ->badge()
                                    ->color(fn (?string $state): string => match ($state ?? '') {
                                        'actif' => 'success',
                                        'inactif' => 'danger',
                                        default => 'gray',
                                    }),
                                TextEntry::make('description')
                                    ->label('Description'),
                            ]),
                    ]),
                
                Section::make('Adresse')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('adresse')
                                    ->label('Adresse'),
                                TextEntry::make('code_postal')
                                    ->label('Code postal'),
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('ville')
                                    ->label('Ville'),
                                TextEntry::make('pays')
                                    ->label('Pays'),
                            ]),
                    ]),
                
                Section::make('Contact')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('contact_nom')
                                    ->label('Nom du contact'),
                                TextEntry::make('contact_email')
                                    ->label('Email du contact')
                                    ->url(fn (?string $state): ?string => $state ? "mailto:{$state}" : null),
                                TextEntry::make('contact_telephone')
                                    ->label('Téléphone du contact')
                                    ->url(fn (?string $state): ?string => $state ? "tel:{$state}" : null),
                            ]),
                    ]),
                
                Section::make('Geofencing')
                    ->schema([
                        Grid::make(1)
                            ->schema([
                                IconEntry::make('has_geofencing')
                                    ->label('Geofencing activé')
                                    ->boolean(),
                            ]),
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('latitude')
                                    ->label('Latitude'),
                                TextEntry::make('longitude')
                                    ->label('Longitude'),
                                TextEntry::make('rayon_geofencing')
                                    ->label('Rayon (mètres)'),
                            ])
                            ->visible(fn ($record) => $record->has_geofencing),
                    ]),
                
                Section::make('Horaires d\'ouverture')
                    ->schema([
                        KeyValueEntry::make('horaires')
                            ->label('Horaires')
                    ]),
                
                Section::make('Informations système')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('created_at')
                                    ->label('Créé le')
                                    ->dateTime(),
                                TextEntry::make('updated_at')
                                    ->label('Mis à jour le')
                                    ->dateTime(),
                            ]),
                    ])
                    ->collapsed(),
            ]);
    }
}
