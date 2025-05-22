<?php

namespace App\Filament\Resources\DepartementResource\Pages;

use App\Filament\Resources\DepartementResource;
use Filament\Resources\Pages\CreateRecord;
use App\Models\Departement;

class CreateDepartement extends CreateRecord
{
    protected static string $resource = DepartementResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = auth()->user();
        
        // Si l'utilisateur n'est pas SuperAdmin ou Support, on utilise son entreprise_id
        if (!$user->isSuperAdmin() && !$user->isSupport()) {
            $data['entreprise_id'] = $user->entreprise_id;
        }
        
        // Générer un code automatiquement si non fourni
        if (empty($data['code']) && !empty($data['nom']) && !empty($data['filiale_id'])) {
            $data['code'] = Departement::genererCode($data['nom'], $data['filiale_id']);
        }

        // Définir le niveau à 1 par défaut pour les nouveaux départements
        // Le niveau sera recalculé après la création
        $data['niveau'] = 1;

        return $data;
    }

    protected function afterCreate(): void
    {
        // Recalculer le niveau hiérarchique après la création
        $this->record->calculerEtMettreAJourNiveau();
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
