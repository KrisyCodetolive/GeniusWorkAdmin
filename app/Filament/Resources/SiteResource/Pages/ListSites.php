<?php

namespace App\Filament\Resources\SiteResource\Pages;

use App\Filament\Resources\SiteResource;
use App\Filament\Resources\SiteResource\Widgets\SiteStatsWidget;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSites extends ListRecords
{
    protected static string $resource = SiteResource::class;

    protected function getHeaderActions(): array
    {
        $user = auth()->user();
        $actions = [
            Actions\CreateAction::make()
                ->label(function () {
                    $entrepriseId = auth()->user()->entreprise_id;
                    $sitesUtilises = \App\Models\Site::where('entreprise_id', $entrepriseId)->count();
                    $sitesMax = 10; // Limite maximale de sites
                    $sitesRestants = max(0, $sitesMax - $sitesUtilises);
                    
                    return $sitesRestants . ' Sites Restants';
                })
                ->icon('heroicon-o-building-office-2')
                ->color(function () {
                    $entrepriseId = auth()->user()->entreprise_id;
                    $sitesUtilises = \App\Models\Site::where('entreprise_id', $entrepriseId)->count();
                    $sitesMax = 10; // Limite maximale de sites
                    $sitesRestants = max(0, $sitesMax - $sitesUtilises);
                    
                    // Rouge si moins de 20% restants, orange si moins de 50%, vert sinon
                    if ($sitesRestants <= 2) {
                        return 'danger';
                    } elseif ($sitesRestants <= 5) {
                        return 'warning';
                    } else {
                        return 'success';
                    }
                })
                ->tooltip(function () {
                    $entrepriseId = auth()->user()->entreprise_id;
                    $sitesUtilises = \App\Models\Site::where('entreprise_id', $entrepriseId)->count();
                    $sitesMax = 10; // Limite maximale de sites
                    $sitesRestants = max(0, $sitesMax - $sitesUtilises);
                    
                    return "Vous pouvez encore créer $sitesRestants sites sur un total de $sitesMax";
                })
                ->visible(fn (): bool => auth()->user()->isAdmin() || auth()->user()->isSuperAdmin() || auth()->user()->isSupport()),
                
            Actions\CreateAction::make(),
            // Afficher le Maps ici 
            Actions\Action::make('viewMap')
                ->label('Carte des sites')
                ->icon('heroicon-o-map')
                ->color('success')
                ->url('/admin/site-map')
                ,
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
                            ->body('Vous devez être associé à une entreprise pour générer des exemples de sites.')
                            ->danger()
                            ->send();
                        return;
                    }
                    
                    // Générer les exemples de sites
                    $result = \Database\Seeders\SiteExempleSeeder::createForEntreprise($entreprise);
                    
                    // Notification de succès
                    \Filament\Notifications\Notification::make()
                        ->title('Exemples générés')
                        ->body(count($result['created']) . ' sites exemples ont été créés pour votre entreprise.' . 
                               ($result['existants'] > 0 ? ' ' . $result['existants'] . ' sites existaient déjà.' : ''))
                        ->success()
                        ->send();
                })
                ->requiresConfirmation()
                ->modalHeading('Générer des exemples de sites')
                ->modalDescription('Cette action va créer des sites exemples pour votre entreprise. Vous pourrez les modifier ou les supprimer par la suite.')
                ->modalSubmitActionLabel('Générer');
        }

        return $actions;
    }
    
    protected function getHeaderWidgets(): array
    {
        return [
            SiteStatsWidget::class,
        ];
    }
}
