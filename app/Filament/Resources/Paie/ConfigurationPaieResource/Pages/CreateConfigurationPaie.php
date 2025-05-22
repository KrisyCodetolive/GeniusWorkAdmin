<?php

namespace App\Filament\Resources\Paie\ConfigurationPaieResource\Pages;

use App\Filament\Resources\Paie\ConfigurationPaieResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;

class CreateConfigurationPaie extends CreateRecord
{
    protected static string $resource = ConfigurationPaieResource::class;
    
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['entreprise_id'] = Auth::user()->entreprise_id;
        
        return $data;
    }
    
    protected function handleRecordCreation(array $data): Model
    {
        // Si cette configuration est définie comme par défaut, désactiver les autres configurations par défaut
        if ($data['est_defaut']) {
            $this->getModel()::where('entreprise_id', $data['entreprise_id'])
                ->where('est_defaut', true)
                ->update(['est_defaut' => false]);
        }
        
        return static::getModel()::create($data);
    }
    
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->record]);
    }
}
