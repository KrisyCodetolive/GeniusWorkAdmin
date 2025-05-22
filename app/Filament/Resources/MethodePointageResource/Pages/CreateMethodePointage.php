<?php

namespace App\Filament\Resources\MethodePointageResource\Pages;

use App\Filament\Resources\MethodePointageResource;
use Filament\Resources\Pages\CreateRecord;

class CreateMethodePointage extends CreateRecord
{
    protected static string $resource = MethodePointageResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
    
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = auth()->user();
        
        // Si l'utilisateur n'est pas SuperAdmin ou Support, utiliser son entreprise_id
        if (!$user->isSuperAdmin() && !$user->isSupport()) {
            $data['entreprise_id'] = $user->entreprise_id;
        }
        
        return $data;
    }
}
