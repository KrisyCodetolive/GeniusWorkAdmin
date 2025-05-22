<?php

namespace App\Filament\Resources\ParametresNotificationResource\Pages;

use App\Filament\Resources\ParametresNotificationResource;
use App\Models\ParametresNotification;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;

class ListParametresNotifications extends ListRecords
{
    protected static string $resource = ParametresNotificationResource::class;

    protected function getHeaderActions(): array
    {
        $user = Auth::user();
        
        // Si l'utilisateur est SuperAdmin ou Support, il peut créer des paramètres sans restriction
        if ($user->isSuperAdmin() || $user->isSupport()) {
            return [
                Actions\CreateAction::make(),
            ];
        }
        
        // Pour les Admin, vérifier si leur entreprise a déjà des paramètres de notification
        $entrepriseId = $user->entreprise_id;
        $parametresExistent = ParametresNotification::where('entreprise_id', $entrepriseId)->exists();
        
        // Ne pas afficher le bouton de création si des paramètres existent déjà
        if ($parametresExistent) {
            return [];
        }
        
        return [
            Actions\CreateAction::make(),
        ];
    }
}
