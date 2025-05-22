<?php

namespace App\Filament\Resources\FilialeResource\Pages;

use App\Filament\Resources\FilialeResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Infolist;

class ViewFiliale extends ViewRecord
{
    protected static string $resource = FilialeResource::class;

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
                                    ->label('Nom de la filiale'),
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('code')
                                    ->label('Code'),
                                TextEntry::make('statut')
                                    ->badge()
                                    ->color(fn (string $state): string => match ($state) {
                                        'actif' => 'success',
                                        'inactif' => 'danger',
                                        default => 'gray',
                                    }),
                            ]),
                        TextEntry::make('description')
                            ->label('Description')
                            ->columnSpanFull(),
                    ]),
                
                Section::make('Adresse et contact')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('adresse')
                                    ->label('Adresse'),
                                TextEntry::make('ville')
                                    ->label('Ville'),
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('pays')
                                    ->label('Pays'),
                                TextEntry::make('telephone')
                                    ->label('Téléphone'),
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('email')
                                    ->label('Email'),
                                TextEntry::make('site_web')
                                    ->label('Site web')
                                    ->url(fn (mixed $state): ?string => $state),
                            ]),
                    ]),
                
                Section::make('Site associé')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('site.nom')
                                    ->label('Nom du site'),
                                TextEntry::make('site.adresse')
                                    ->label('Adresse du site'),
                            ]),
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('site.ville')
                                    ->label('Ville'),
                                TextEntry::make('site.code_postal')
                                    ->label('Code postal'),
                                TextEntry::make('site.pays')
                                    ->label('Pays'),
                            ]),
                    ]),
                
                Section::make('Logo et configuration')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                ImageEntry::make('logo')
                                    ->label('Logo')
                                    ->disk('public')
                                    ->height(150)
                                    ->width(150)
                                    ->circular(),
                                TextEntry::make('configuration')
                                    ->label('Configuration')
                                    ->formatStateUsing(fn ($state) => $state ? json_encode(json_decode($state), JSON_PRETTY_PRINT) : null)
                                    ->prose(),
                            ]),
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
