<?php

namespace App\Filament\Resources\PolitiqueResource\Pages;

use App\Filament\Resources\PolitiqueResource;
use App\Models\Politique;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;

class ListPolitiques extends ListRecords
{
    protected static string $resource = PolitiqueResource::class;

    protected function getHeaderActions(): array
    {
        $user = Auth::user();
        
        // Si l'utilisateur est SuperAdmin ou Support, il peut créer des politiques sans restriction
        if ($user->isSuperAdmin() || $user->isSupport()) {
            return [
                Actions\CreateAction::make(),
            ];
        }
        
        // Pour les Admin, vérifier si leur entreprise a déjà une politique
        $entrepriseId = $user->entreprise_id;
        $politiqueExiste = Politique::where('entreprise_id', $entrepriseId)->exists();
        
        // Ne pas afficher le bouton de création si une politique existe déjà
        if ($politiqueExiste) {
            return [];
        }
        
        return [
            Actions\CreateAction::make(),
        ];
    }
}
