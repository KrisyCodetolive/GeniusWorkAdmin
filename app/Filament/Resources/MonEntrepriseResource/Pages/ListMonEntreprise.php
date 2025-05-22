<?php

namespace App\Filament\Resources\MonEntrepriseResource\Pages;

use App\Filament\Resources\MonEntrepriseResource;
use App\Filament\Widgets\EntrepriseStatsWidget;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;

class ListMonEntreprise extends ListRecords
{
    protected static string $resource = MonEntrepriseResource::class;

    protected function getHeaderActions(): array
    {
        $user = Auth::user();
        
        // Seuls les SuperAdmin et Support peuvent créer des entreprises
        if ($user && $user->isSuperAdmin() || $user->isSupport()) {
            return [
                Actions\CreateAction::make(),
            ];
        }
        
        return [];
    }


    public function mount(): void
    {
        $user = Auth::user();
        
        // Si l'utilisateur a une entreprise et n'est pas SuperAdmin/Support, 
        // rediriger directement vers la page de visualisation
        if ($user && $user->entreprise_id && !$user->hasRole(['SuperAdmin', 'Support'])) {
            $this->redirect(MonEntrepriseResource::getUrl('view', ['record' => $user->entreprise_id]));
            return;
        }
        
        // Si l'utilisateur n'a pas d'entreprise et n'est pas SuperAdmin/Support,
        // rediriger vers la page d'accueil
        if ($user && !$user->entreprise_id && !$user->hasRole(['SuperAdmin', 'Support'])) {
            $this->redirect('/');
            return;
        }
        
        parent::mount();
    }
}
