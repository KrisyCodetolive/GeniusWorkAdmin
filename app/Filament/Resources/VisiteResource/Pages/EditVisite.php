<?php

namespace App\Filament\Resources\VisiteResource\Pages;

use App\Filament\Resources\VisiteResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Forms;
use App\Models\Visiteur;
use App\Models\Site;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Services\Visite\VisiteService;
use Filament\Notifications\Notification;

class EditVisite extends EditRecord
{
    protected static string $resource = VisiteResource::class;
    
    /**
     * @var VisiteService
     */
    protected $visiteService;
    
    /**
     * Initialise les services lors du montage de la page
     */
    public function mount(string|int $record): void
    {
        parent::mount($record);
        // Initialize services
        $this->visiteService = app(\App\Services\Visite\VisiteService::class);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            
            Actions\Action::make('terminer')
                ->label('Terminer la visite')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->visible(fn () => $this->record->statut === 'en_cours')
                ->action(function () {
                    $this->visiteService->terminerVisite($this->record);
                    
                    Notification::make()
                        ->title('Visite terminée')
                        ->body('La visite a été marquée comme terminée.')
                        ->success()
                        ->send();
                        
                    $this->redirect($this->getResource()::getUrl('index'));
                })
                ->requiresConfirmation(),
                
            Actions\Action::make('annuler')
                ->label('Annuler la visite')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->visible(fn () => $this->record->statut === 'en_cours')
                ->action(function () {
                    $this->visiteService->annulerVisite($this->record);
                    
                    Notification::make()
                        ->title('Visite annulée')
                        ->body('La visite a été marquée comme annulée.')
                        ->success()
                        ->send();
                        
                    $this->redirect($this->getResource()::getUrl('index'));
                })
                ->requiresConfirmation(),
        ];
    }
    
    // Le formulaire est maintenant défini dans la classe VisiteResource
    
    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Si le statut a changé à 'terminee' et qu'il n'y a pas de date de départ, ajouter la date actuelle
        if (isset($data['statut']) && $data['statut'] === 'terminee' && empty($data['date_depart'])) {
            $data['date_depart'] = now();
        }
        
        return $data;
    }
    
    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Visite mise à jour')
            ->body('La visite a été mise à jour avec succès.');
    }
}
