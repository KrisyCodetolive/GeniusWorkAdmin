<?php

namespace App\Filament\Resources\UtilisateurRHResource\Pages;

use App\Filament\Resources\UtilisateurRHResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateUtilisateurRH extends CreateRecord
{
    protected static string $resource = UtilisateurRHResource::class;
    
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
    
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Assigner automatiquement l'entreprise_id de l'utilisateur connecté
        $data['entreprise_id'] = Auth::user()->entreprise_id;
        
        return $data;
    }
    
    protected function getCreatedNotificationTitle(): ?string
    {
        return "L'utilisateur RH a été créé avec succès";
    }
}
