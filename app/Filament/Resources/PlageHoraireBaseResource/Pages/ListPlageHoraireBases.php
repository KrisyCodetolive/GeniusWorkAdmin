<?php

namespace App\Filament\Resources\PlageHoraireBaseResource\Pages;

use App\Filament\Resources\PlageHoraireBaseResource;
use App\Filament\Resources\JourResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Notifications\Notification;

class ListPlageHoraireBases extends ListRecords
{
    protected static string $resource = PlageHoraireBaseResource::class;

    protected function getHeaderActions(): array
    {
        $user = auth()->user();
        $actions = [
            Actions\Action::make('planningJournalier')
                ->label('Planning journalier')
                ->icon('heroicon-o-calendar')
                ->color('warning')
                ->url(fn (): string => JourResource::getUrl())
                ->visible(fn (): bool => auth()->user()->isAdmin() || auth()->user()->isSuperAdmin() || auth()->user()->isSupport()),
            Actions\CreateAction::make()
                ->label('Nouvelle plage horaire')
                ->icon('heroicon-o-plus'),
        ];

        // Ajouter l'action pour générer des exemples si l'utilisateur est associé à une entreprise
        // et n'est pas SuperAdmin ou Support
        if ($user->entreprise_id && !$user->isSuperAdmin() && !$user->isSupport()) {
            $actions[] = Actions\Action::make('genererExemples')
                ->label('Générer des exemples')
                ->icon('heroicon-o-clock')
                ->action(function () use ($user) {
                    $entreprise = $user->entreprise;
                    
                    if (!$entreprise) {
                        Notification::make()
                            ->title('Erreur')
                            ->body('Vous devez être associé à une entreprise pour générer des exemples de plages horaires.')
                            ->danger()
                            ->send();
                        return;
                    }
                    
                    // Générer les exemples de plages horaires
                    $result = \Database\Seeders\PlageHoraireExempleSeeder::createForEntreprise($entreprise);
                    
                    // Notification de succès
                    Notification::make()
                        ->title('Exemples générés')
                        ->body(count($result['created']) . ' plages horaires exemples ont été créées pour votre entreprise.' . 
                               ($result['existants'] > 0 ? ' ' . $result['existants'] . ' plages horaires existaient déjà.' : ''))
                        ->success()
                        ->send();
                    
                })
                ->requiresConfirmation()
                ->modalHeading('Générer des exemples de plages horaires')
                ->modalDescription('Cette action va créer des plages horaires exemples pour votre entreprise. Vous pourrez les modifier ou les supprimer par la suite.')
                ->modalSubmitActionLabel('Générer');
        }

        return $actions;
    }
}
