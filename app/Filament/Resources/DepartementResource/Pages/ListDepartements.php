<?php

namespace App\Filament\Resources\DepartementResource\Pages;

use App\Filament\Resources\DepartementResource;
use App\Filament\Resources\DepartementResource\Widgets\DepartementStatsWidget;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDepartements extends ListRecords
{
    protected static string $resource = DepartementResource::class;

    protected function getHeaderActions(): array
    {
        $user = auth()->user();
        $actions = [
            Actions\CreateAction::make(),
            // Page vue Stats pour les départements
            Actions\Action::make('stats')
                ->label('Voir les stats')
                ->icon('heroicon-o-chart-bar')
                ->color('success')
                ->url(fn () => route('filament.admin.pages.departements-stats')),
        ];


        // Ajouter l'action pour générer des exemples si l'utilisateur est associé à une entreprise
        // et n'est pas SuperAdmin ou Support
        if ($user->entreprise_id && !$user->isSuperAdmin() && !$user->isSupport()) {
            $actions[] = Actions\Action::make('genererExemples')
                ->label('Générer des exemples')
                ->icon('heroicon-o-building-office-2')
                ->action(function () use ($user) {
                    $entreprise = $user->entreprise;
                    
                    if (!$entreprise) {
                        \Filament\Notifications\Notification::make()
                            ->title('Erreur')
                            ->body('Vous devez être associé à une entreprise pour générer des exemples de départements.')
                            ->danger()
                            ->send();
                        return;
                    }
                    
                    // Vérifier si l'entreprise a au moins une filiale
                    $filiale = $entreprise->filiales()->first();
                    
                    if (!$filiale) {
                        \Filament\Notifications\Notification::make()
                            ->title('Erreur')
                            ->body('Vous devez avoir au moins une filiale pour générer des exemples de départements.')
                            ->danger()
                            ->send();
                        return;
                    }
                    
                    // Générer les exemples de départements
                    $result = \Database\Seeders\DepartementExempleSeeder::createForFiliale($filiale);
                    
                    // Notification de succès
                    \Filament\Notifications\Notification::make()
                        ->title('Exemples générés')
                        ->body(count($result['created']) . ' départements exemples ont été créés pour votre filiale "' . $filiale->nom . '".' . 
                               ($result['existants'] > 0 ? ' ' . $result['existants'] . ' départements existaient déjà.' : ''))
                        ->success()
                        ->send();
                })
                ->requiresConfirmation()
                ->modalHeading('Générer des exemples de départements')
                ->modalDescription('Cette action va créer des départements exemples pour la première filiale de votre entreprise. Vous pourrez les modifier ou les supprimer par la suite.')
                ->modalSubmitActionLabel('Générer');
        }

        return $actions;
    }
    
    protected function getHeaderWidgets(): array
    {
        return [
            DepartementStatsWidget::class,
        ];
    }
}
