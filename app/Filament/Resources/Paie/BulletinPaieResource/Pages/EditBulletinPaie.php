<?php

namespace App\Filament\Resources\Paie\BulletinPaieResource\Pages;

use App\Filament\Resources\Paie\BulletinPaieResource;
use App\Interfaces\Paie\CalculPaieServiceInterface;
use App\Models\Employeur;
use App\Models\Paie\ConfigurationPaie;
use App\Models\Paie\ElementPaie;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditBulletinPaie extends EditRecord
{
    protected static string $resource = BulletinPaieResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make()
                ->visible(fn () => $this->record->statut === 'brouillon'),
        ];
    }
    
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->record]);
    }
    
    protected function beforeSave(): void
    {
        // Vérifier que le bulletin est en brouillon
        if ($this->record->statut !== 'brouillon') {
            $this->halt();
            $this->notify('danger', 'Seuls les bulletins en brouillon peuvent être modifiés.');
        }
    }
    
    protected function afterSave(): void
    {
        // Ne recalculer que si le calcul automatique est activé
        if ($this->data['calcul_auto'] ?? false) {
            // Récupérer le service de calcul de paie
            $calculPaieService = app(CalculPaieServiceInterface::class);
            
            // Récupérer l'employé et la configuration
            $employeur = Employeur::find($this->data['employeur_id']);
            $configuration = ConfigurationPaie::find($this->data['configuration_paie_id']);
            
            if (!$employeur || !$configuration) {
                return;
            }
            
            // Préparer les éléments supplémentaires
            $elementsSupplementaires = [
                'salaire_base' => $this->data['salaire_base'] ?? 0,
                'indemnites' => $this->data['indemnites'] ?? [],
                'primes' => $this->data['primes'] ?? [],
                'retenues' => $this->data['retenues'] ?? [],
            ];
            
            // Supprimer les anciens éléments de paie
            ElementPaie::where('bulletin_paie_id', $this->record->id)->delete();
            
            // Calculer le salaire brut
            $salaireBrut = $calculPaieService->calculerSalaireBrut($employeur, $configuration, $elementsSupplementaires);
            
            // Calculer les retenues
            $retenues = $calculPaieService->calculerRetenues($employeur, $configuration, $salaireBrut, $elementsSupplementaires);
            
            // Calculer le salaire net
            $salaireNet = $calculPaieService->calculerSalaireNet($salaireBrut, $retenues['total']);
            
            // Calculer les charges patronales
            $chargesPatronales = $calculPaieService->calculerChargesPatronales($employeur, $configuration, $salaireBrut);
            
            // Mettre à jour le bulletin
            $this->record->update([
                'salaire_base' => $this->data['salaire_base'],
                'salaire_brut' => $salaireBrut,
                'total_indemnites' => $calculPaieService->calculerTotalIndemnites($elementsSupplementaires['indemnites'], $this->data['salaire_base']),
                'total_primes' => $calculPaieService->calculerTotalPrimes($elementsSupplementaires['primes'], $this->data['salaire_base']),
                'cnps_employe' => $retenues['cnps_employe'],
                'igr' => $retenues['igr'],
                'total_retenues' => $retenues['total'],
                'salaire_net' => $salaireNet,
                'charges_patronales' => $chargesPatronales['total'],
                'cnps_employeur' => $chargesPatronales['cnps_employeur'],
                'prestations_familiales' => $chargesPatronales['prestations_familiales'],
                'accident_travail' => $chargesPatronales['accident_travail'],
                'assurance_maladie' => $chargesPatronales['assurance_maladie'],
            ]);
            
            // Créer les nouveaux éléments de paie
            $calculPaieService->creerElementsPaie($this->record, $elementsSupplementaires);
            
            $this->notify('success', 'Le bulletin de paie a été recalculé avec succès.');
        }
    }
}
