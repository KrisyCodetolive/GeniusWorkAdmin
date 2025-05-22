<?php

namespace App\Filament\Resources\Paie\BulletinPaieResource\Pages;

use App\Filament\Resources\Paie\BulletinPaieResource;
use App\Filament\Resources\Paie\ConfigurationPaieResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBulletinPaies extends ListRecords
{
    protected static string $resource = BulletinPaieResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Nouveau bulletin'),
                
            Actions\Action::make('generer_bulletin')
                ->label('Générer un bulletin')
                ->icon('heroicon-o-document-plus')
                ->color('success')
                ->url(fn (): string => route('paie.bulletins.wizard.generate')),
                
            Actions\Action::make('generer_bulletins_masse')
                ->label('Génération en masse')
                ->icon('heroicon-o-document-duplicate')
                ->color('warning')
                ->url(fn (): string => route('paie.bulletins.wizard.generate-masse')),
                   
            Actions\Action::make('configurations')
                ->label('Configurations')
                ->icon('heroicon-o-cog')
                ->color('danger')
                ->url(fn (): string => ConfigurationPaieResource::getUrl())
                ->visible(fn (): bool => auth()->user()->isAdmin() || auth()->user()->isSuperAdmin() || auth()->user()->isSupport()),
        ];
    }
}
