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
