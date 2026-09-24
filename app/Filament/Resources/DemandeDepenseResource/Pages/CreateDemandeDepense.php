<?php

namespace App\Filament\Resources\DemandeDepenseResource\Pages;

use App\Filament\Resources\DemandeDepenseResource;
use App\Models\DemandeDepense;
use Filament\Resources\Pages\CreateRecord;

class CreateDemandeDepense extends CreateRecord
{
    use CreateRecord\Concerns\HasWizard;

    protected static string $resource = DemandeDepenseResource::class;

    protected function getSteps(): array
    {
        return DemandeDepenseResource::etapes(avecJustificatifs: true);
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data = DemandeDepenseResource::forcerEntreprise($data);
        $data['cree_par_user_id'] = auth()->id();
        $data['statut'] = DemandeDepense::STATUT_BROUILLON;

        return $data;
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Brouillon enregistré. Soumettez la demande pour lancer la validation.';
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->getRecord()]);
    }
}
