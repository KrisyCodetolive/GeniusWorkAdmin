<?php

namespace App\Filament\Resources\JourResource\Pages;

use App\Filament\Resources\JourResource;
use App\Models\Employeur;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateJour extends CreateRecord
{
    protected static string $resource = JourResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = Auth::user();
        
        // Si l'utilisateur n'est pas SuperAdmin ou Support, on utilise son entreprise_id
        if (!$user->isSuperAdmin() && !$user->isSupport()) {
            $data['entreprise_id'] = $user->entreprise_id;
        } else if (!isset($data['entreprise_id'])) {
            // Pour les SuperAdmin et Support, si l'entreprise n'est pas spécifiée,
            // on utilise l'entreprise de l'employé sélectionné
            if (isset($data['employeur_id'])) {
                $employeur = Employeur::find($data['employeur_id']);
                if ($employeur) {
                    $data['entreprise_id'] = $employeur->entreprise_id;
                }
            }
        }
        
        return $data;
    }
}
