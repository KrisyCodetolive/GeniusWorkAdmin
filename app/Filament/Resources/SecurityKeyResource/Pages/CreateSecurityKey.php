<?php

namespace App\Filament\Resources\SecurityKeyResource\Pages;

use App\Filament\Resources\SecurityKeyResource;
use App\Models\SecurityKey;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;

class CreateSecurityKey extends CreateRecord
{
    protected static string $resource = SecurityKeyResource::class;
    
    protected function mutateFormData(array $data): array
    {
        // Générer une nouvelle clé aléatoire
        $key = Str::random(32);
        
        // Désactiver toutes les clés actives si la nouvelle clé est active
        if (isset($data['is_active']) && $data['is_active']) {
            SecurityKey::where('is_active', true)->update(['is_active' => false]);
        }
        
        // Ajouter les données manquantes
        $data['key_encrypted'] = Crypt::encryptString($key);
        $data['generated_at'] = now();
        $data['generated_by'] = $data['generated_by'] ?? Auth::id();
        
        return $data;
    }
    
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
    
    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Clé de sécurité créée')
            ->body('La clé de sécurité a été créée avec succès.');
    }
}
