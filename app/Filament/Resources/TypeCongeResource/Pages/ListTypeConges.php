<?php

namespace App\Filament\Resources\TypeCongeResource\Pages;

use App\Filament\Resources\TypeCongeResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTypeConges extends ListRecords
{
    protected static string $resource = TypeCongeResource::class;

    protected function getHeaderActions(): array
    {
        $user = auth()->user();
        $isSuperAdminOrSupport = $user->isSuperAdmin() || $user->isSupport();
        
        return [
            Actions\CreateAction::make(),
            Actions\Action::make('genererExemples')
                ->label('Générer des exemples')
                ->icon('heroicon-o-sparkles')
                ->color('success')
                ->action(function () {
                    $user = auth()->user();
                    $entreprise = $user->entreprise;
                    
                    // Vérifier que l'utilisateur a une entreprise associée
                    if (!$entreprise) {
                        \Filament\Notifications\Notification::make()
                            ->title('Erreur')
                            ->body('Vous devez être associé à une entreprise pour générer des exemples de types de congés.')
                            ->danger()
                            ->send();
                        return;
                    }
                    
                    // Générer les exemples de types de congés
                    $result = \Database\Seeders\TypeCongeExempleSeeder::createForEntreprise($entreprise);
                    
                    // Notification de succès
                    \Filament\Notifications\Notification::make()
                        ->title('Exemples générés')
                        ->body(count($result['created']) . ' types de congés exemples ont été créés pour votre entreprise.' . 
                               ($result['existants'] > 0 ? ' ' . $result['existants'] . ' types de congés existaient déjà.' : ''))
                        ->success()
                        ->send();
                    
                })
                ->requiresConfirmation()
                ->modalHeading('Générer des exemples de types de congés')
                ->modalDescription('Cette action va créer des exemples de types de congés pour votre entreprise. Vous pourrez les modifier ou les supprimer par la suite.')
                ->modalSubmitActionLabel('Générer')
                ->visible(function () use ($isSuperAdminOrSupport) {
                    $user = auth()->user();
                    // Visible seulement pour les utilisateurs avec une entreprise et qui ne sont pas SuperAdmin ou Support
                    return $user->entreprise_id && !$isSuperAdminOrSupport;
                }),
        ];
    }
}
