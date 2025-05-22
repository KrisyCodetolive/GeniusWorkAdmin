<?php

namespace App\Filament\Resources\EmployeurResource\Pages;

use App\Filament\Resources\EmployeurResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateEmployeur extends CreateRecord
{
    protected static string $resource = EmployeurResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
    
    protected function handleRecordCreation(array $data): Model
    {
        $user = auth()->user();
        
        // Si l'utilisateur n'est pas SuperAdmin ou Support, on utilise son entreprise_id
        if (!$user->isSuperAdmin() && !$user->isSupport()) {
            $data['entreprise_id'] = $user->entreprise_id;
        }
        
        // Stocker les données du formulaire dans la session pour le listener
        session()->put('employeur_form_data', $data);
        
        return static::getModel()::create($data);
    }
}
