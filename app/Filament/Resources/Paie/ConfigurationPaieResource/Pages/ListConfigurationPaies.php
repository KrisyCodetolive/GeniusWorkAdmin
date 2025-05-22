<?php

namespace App\Filament\Resources\Paie\ConfigurationPaieResource\Pages;

use App\Filament\Actions\GenerateConfigurationPaieExemplesAction;
use App\Filament\Resources\Paie\ConfigurationPaieResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListConfigurationPaies extends ListRecords
{
    protected static string $resource = ConfigurationPaieResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Nouvelle configuration'),
                
            GenerateConfigurationPaieExemplesAction::make()
                ->label('Générer des exemples')
                ->visible(function () {
                    $user = auth()->user();
                    $entreprise = $user->entreprise;
                    
                    if (!$entreprise) {
                        return false;
                    }
                    
                    // Vérifier si l'entreprise a déjà des configurations de paie
                    $existingConfigs = \App\Models\Paie\ConfigurationPaie::where('entreprise_id', $entreprise->id)->count();
                    
                    // Ne montrer l'action que si l'entreprise n'a pas encore de configurations
                    return $existingConfigs === 0;
                }),
        ];
    }
}
