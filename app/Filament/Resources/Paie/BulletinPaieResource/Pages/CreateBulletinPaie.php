<?php

namespace App\Filament\Resources\Paie\BulletinPaieResource\Pages;

use App\Filament\Resources\Paie\BulletinPaieResource;
use App\Interfaces\Paie\CalculPaieServiceInterface;
use App\Models\Employeur;
use App\Models\Paie\ConfigurationPaie;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class CreateBulletinPaie extends CreateRecord
{
    protected static string $resource = BulletinPaieResource::class;
    
    protected function handleRecordCreation(array $data): Model
    {
        // Récupérer l'employeur et la configuration de paie
        $employeur = Employeur::findOrFail($data['employeur_id']);
        $configuration = ConfigurationPaie::findOrFail($data['configuration_paie_id']);
        
        // Préparer les éléments supplémentaires
        $elementsSupplementaires = [
            'salaire_base' => $data['salaire_base'] ?? $employeur->salaire_base ?? 0,
            'indemnites' => $data['indemnites'] ?? [],
            'primes' => $data['primes'] ?? [],
            'retenues' => $data['retenues'] ?? [],
        ];
        
        // Préparer les paramètres pour le service de calcul de paie
        $parametres = [
            'periode_debut' => $data['periode_debut'],
            'periode_fin' => $data['periode_fin'],
            'date_paiement' => $data['date_paiement'],
            'genere_par' => Auth::id(),
            'entreprise_id' => Auth::user()->entreprise_id,
            'elements_supplementaires' => $elementsSupplementaires,
            'calcul_auto' => $data['calcul_auto'] ?? true,
        ];
        
        // Générer le bulletin de paie via le service
        $calculPaieService = app(CalculPaieServiceInterface::class);
        $bulletin = $calculPaieService->genererBulletinPaie($employeur, $configuration, $parametres);
        
        return $bulletin;
    }
    
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->record]);
    }
}
