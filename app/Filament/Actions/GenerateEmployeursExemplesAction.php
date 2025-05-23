<?php

namespace App\Filament\Actions;

use App\Models\Entreprise;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;
use Database\Seeders\EmployeurExempleSeeder;

class GenerateEmployeursExemplesAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'genererEmployesExemples';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Générer des employés exemples')
            ->icon('heroicon-o-user-plus')
            ->color('warning')
            ->requiresConfirmation()
            ->modalHeading('Générer des employés exemples')
            ->modalDescription('Cette action va créer des employés exemples pour votre entreprise. Vous pourrez les modifier ou les supprimer par la suite.')
            ->modalSubmitActionLabel('Générer')
            ->action(function (): void {
                $user = auth()->user();
                $entreprise = $user->entreprise;
                
                if (!$entreprise) {
                    Notification::make()
                        ->title('Erreur')
                        ->body('Vous devez être associé à une entreprise pour générer des exemples d\'employés.')
                        ->danger()
                        ->send();
                    return;
                }
                
                // Vérifier si l'entreprise a un abonnement actif
                if (!$entreprise->abonnementActif) {
                    Notification::make()
                        ->title('Erreur')
                        ->body('Votre entreprise n\'a pas d\'abonnement actif. Veuillez souscrire à un abonnement avant de générer des employés exemples.')
                        ->danger()
                        ->send();
                    return;
                }
                
                // Vérifier la limite d'employés pour l'entreprise
                $currentEmployeeCount = $entreprise->getEmployeCount();
                $limit = $entreprise->abonnementActif->nombre_personnels ?? 
                        $entreprise->abonnementActif->planAbonnement->nombre_employes_max ?? 0;
                
                // Nombre d'employés exemples à créer (basé sur le seeder)
                $nombreEmployesExemples = 10; // Estimation du nombre d'employés dans le seeder
                
                // Vérifier si la génération d'employés exemples dépasserait la limite
                if (($currentEmployeeCount + $nombreEmployesExemples) > $limit) {
                    Notification::make()
                        ->title('Limite d\'employés atteinte')
                        ->body('Votre abonnement actuel permet un maximum de ' . $limit . ' employés. Vous avez déjà ' . $currentEmployeeCount . ' employés. La génération d\'exemples dépasserait cette limite. Veuillez mettre à niveau votre abonnement pour ajouter plus d\'employés.')
                        ->warning()
                        ->send();
                    return;
                }
                
                // Générer les exemples d'employés
                $result = EmployeurExempleSeeder::createForEntreprise($entreprise);
                
                // Déterminer le type de notification en fonction du résultat
                $notificationType = count($result['created']) > 0 ? 'success' : 'warning';
                
                // Créer la notification avec le message du seeder
                $notification = Notification::make()
                    ->title(count($result['created']) > 0 ? 'Employés exemples générés' : 'Information')
                    ->body($result['message'])
                    ->$notificationType();
                
                $notification->send();
            });
    }

    public static function make(?string $name = null): static
    {
        $action = parent::make($name);
        
        // Vérifier si l'utilisateur a le droit d'utiliser cette action
        $user = auth()->user();
        if (!$user || !$user->entreprise_id || $user->isSuperAdmin() || $user->isSupport()) {
            $action->hidden();
        }
        
        return $action;
    }
}
