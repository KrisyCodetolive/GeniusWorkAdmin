<?php

namespace App\Filament\Resources\EmployeurResource\Pages;

use App\Filament\Resources\EmployeurResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Filament\Notifications\Notification;
use App\Models\Entreprise;

class CreateEmployeur extends CreateRecord
{
    protected static string $resource = EmployeurResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
    
    protected function handleRecordCreation(array $data): Model
    {
        $user = auth()->user();
        
        // Si l'utilisateur n'est pas SuperAdmin ou Support, on utilise son entreprise_id
        if (!$user->isSuperAdmin() && !$user->isSupport()) {
            $data['entreprise_id'] = $user->entreprise_id;
        }
        
        // Vérifier la limite d'employés pour l'entreprise
        $entreprise = Entreprise::find($data['entreprise_id']);
        
        if ($entreprise && $this->isOverEmployeeLimit($entreprise)) {
            // Notifier l'utilisateur et annuler la création
            Notification::make()
                ->title('Limite d\'employés atteinte')
                ->body('Votre abonnement actuel ne permet pas de créer plus d\'employés. Veuillez mettre à niveau votre abonnement pour ajouter de nouveaux employés.')
                ->danger()
                ->send();
                
            $this->halt();
        }
        
        // Stocker les données du formulaire dans la session pour le listener
        session()->put('employeur_form_data', $data);
        
        return static::getModel()::create($data);
    }
    
    /**
     * Vérifie si l'entreprise a dépassé sa limite d'employés
     */
    protected function isOverEmployeeLimit(Entreprise $entreprise): bool
    {
        // Si l'utilisateur est SuperAdmin ou Support, on ignore la limite
        if (auth()->user()->isSuperAdmin() || auth()->user()->isSupport()) {
            return false;
        }
        
        // Vérifier si l'entreprise a un abonnement actif
        if (!$entreprise->abonnementActif) {
            return true; // Pas d'abonnement actif, donc pas de création possible
        }
        
        // Récupérer le nombre actuel d'employés
        $currentEmployeeCount = $entreprise->getEmployeCount();
        
        // Récupérer la limite d'employés depuis l'abonnement
        $limit = $entreprise->abonnementActif->nombre_personnels ?? 
                $entreprise->abonnementActif->planAbonnement->nombre_employes_max ?? 0;
        
        // Vérifier si le nombre actuel d'employés atteint la limite
        return $currentEmployeeCount >= $limit;
    }
}
