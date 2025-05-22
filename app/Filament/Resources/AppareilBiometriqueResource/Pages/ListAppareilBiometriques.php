<?php

namespace App\Filament\Resources\AppareilBiometriqueResource\Pages;

use App\Filament\Resources\AppareilBiometriqueResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use App\Models\AppareilBiometrique;

class ListAppareilBiometriques extends ListRecords
{
    protected static string $resource = AppareilBiometriqueResource::class;

    protected function getHeaderActions(): array
    {
        $user = auth()->user();
        $actions = [];
        
        // Vérifier si l'utilisateur a le droit de créer un appareil biométrique
        if ($user->isSuperAdmin() || $user->isSupport() || $user->isAdmin()) {
            $actions[] = Actions\CreateAction::make();
        }
        
        return $actions;
    }
}
