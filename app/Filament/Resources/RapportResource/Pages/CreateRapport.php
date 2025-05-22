<?php

namespace App\Filament\Resources\RapportResource\Pages;

use App\Filament\Resources\RapportResource;
use Filament\Resources\Pages\CreateRecord;
use App\Services\RapportFinancierService;

class CreateRapport extends CreateRecord
{
    protected static string $resource = RapportResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->record]);
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Ajouter l'ID de l'utilisateur connecté
        $data['cree_par'] = auth()->id();
        
        // Générer les données du rapport en fonction du type
        if ($data['type'] === 'financier') {
            $service = app(RapportFinancierService::class);
            
            if (!empty($data['employeur_id'])) {
                // Rapport financier pour une entreprise spécifique
                $entreprise = \App\Models\Entreprise::find($data['employeur_id']);
                $rapport = $service->genererRapportFinancier($entreprise, [
                    'date_debut' => $data['date_debut'],
                    'date_fin' => $data['date_fin'],
                ]);
            } else {
                // Rapport financier global
                $rapport = $service->genererRapportGlobal([
                    'date_debut' => $data['date_debut'],
                    'date_fin' => $data['date_fin'],
                ]);
            }
            
            // Ajouter les données du rapport aux paramètres
            $data['parametres'] = array_merge($data['parametres'] ?? [], $rapport);
        }
        
        return $data;
    }
}
