<?php

namespace App\Filament\Resources\UtilisateurRHResource\Pages;

use App\Filament\Resources\UtilisateurRHResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Colors\Color;

class ListUtilisateursRH extends ListRecords
{
    protected static string $resource = UtilisateurRHResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Ajouter un utilisateur RH')
                ->icon('heroicon-o-user-plus')
                ->color('primary'),
        ];
    }
    
    protected function getHeaderWidgets(): array
    {
        return [
            // Vous pouvez ajouter des widgets ici si nécessaire
        ];
    }
}
