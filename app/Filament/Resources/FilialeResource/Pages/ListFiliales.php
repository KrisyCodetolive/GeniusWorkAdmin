<?php

namespace App\Filament\Resources\FilialeResource\Pages;

use App\Filament\Resources\FilialeResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListFiliales extends ListRecords
{
    protected static string $resource = FilialeResource::class;

    protected function getHeaderActions(): array
    {
        $user = auth()->user();
        $actions = [
            Actions\CreateAction::make()
                ->label(function () {
                    $entrepriseId = auth()->user()->entreprise_id;
                    $filialesUtilisees = \App\Models\Filiale::where('entreprise_id', $entrepriseId)->count();
                    $filialesMax = 10; // Limite maximale de filiales
                    $filialesRestantes = max(0, $filialesMax - $filialesUtilisees);
                    
                    return $filialesRestantes . ' Filiales Restantes';
                })
                ->icon('heroicon-o-building-office-2')
                ->color(function () {
                    $entrepriseId = auth()->user()->entreprise_id;
                    $filialesUtilisees = \App\Models\Filiale::where('entreprise_id', $entrepriseId)->count();
                    $filialesMax = 10; // Limite maximale de filiales
                    $filialesRestantes = max(0, $filialesMax - $filialesUtilisees);
                    
                    // Rouge si moins de 20% restants, orange si moins de 50%, vert sinon
                    if ($filialesRestantes <= 2) {
                        return 'danger';
                    } elseif ($filialesRestantes <= 5) {
                        return 'warning';
                    } else {
                        return 'success';
                    }
                })
                ->tooltip(function () {
                    $entrepriseId = auth()->user()->entreprise_id;
                    $filialesUtilisees = \App\Models\Filiale::where('entreprise_id', $entrepriseId)->count();
                    $filialesMax = 10; // Limite maximale de filiales
                    $filialesRestantes = max(0, $filialesMax - $filialesUtilisees);
                    
                    return "Vous pouvez encore créer $filialesRestantes filiales sur un total de $filialesMax";
                })
                ->visible(fn (): bool => auth()->user()->isAdmin() || auth()->user()->isSuperAdmin() || auth()->user()->isSupport()),
                
            Actions\CreateAction::make(),
        ];

        // Ajouter l'action pour générer des exemples si l'utilisateur est associé à une entreprise
        // et n'est pas SuperAdmin ou Support
        if ($user->entreprise_id && !$user->isSuperAdmin() && !$user->isSupport()) {
            $actions[] = Actions\Action::make('genererExemples')
                ->label('Générer des exemples')
                ->icon('heroicon-o-building-storefront')
                ->action(function () use ($user) {
                    $entreprise = $user->entreprise;
                    
                    if (!$entreprise) {
                        \Filament\Notifications\Notification::make()
                            ->title('Erreur')
                            ->body('Vous devez être associé à une entreprise pour générer des exemples de filiales.')
                            ->danger()
                            ->send();
                        return;
                    }
                    
                    // Générer les exemples de filiales
                    $result = \Database\Seeders\FilialeExempleSeeder::createForEntreprise($entreprise);
                    
                    // Notification de succès
                    \Filament\Notifications\Notification::make()
                        ->title('Exemples générés')
                        ->body(count($result['created']) . ' filiales exemples ont été créées pour votre entreprise.' . 
                               ($result['existants'] > 0 ? ' ' . $result['existants'] . ' filiales existaient déjà.' : ''))
                        ->success()
                        ->send();
                })
                ->requiresConfirmation()
                ->modalHeading('Générer des exemples de filiales')
                ->modalDescription('Cette action va créer des filiales exemples pour votre entreprise. Vous pourrez les modifier ou les supprimer par la suite.')
                ->modalSubmitActionLabel('Générer');
        }

        return $actions;
    }
}
