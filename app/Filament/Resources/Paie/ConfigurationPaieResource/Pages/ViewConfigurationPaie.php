<?php

namespace App\Filament\Resources\Paie\ConfigurationPaieResource\Pages;

use App\Filament\Resources\Paie\ConfigurationPaieResource;
use App\Models\Paie\ConfigurationPaie;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewConfigurationPaie extends ViewRecord
{
    protected static string $resource = ConfigurationPaieResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\Action::make('set_default')
                ->label('Définir par défaut')
                ->icon('heroicon-o-star')
                ->color('warning')
                ->visible(fn () => !$this->record->est_defaut)
                ->action(function () {
                    // Désactiver toutes les autres configurations par défaut
                    ConfigurationPaie::where('entreprise_id', $this->record->entreprise_id)
                        ->where('id', '!=', $this->record->id)
                        ->where('est_defaut', true)
                        ->update(['est_defaut' => false]);
                        
                    // Définir cette configuration comme configuration par défaut
                    $this->record->update(['est_defaut' => true]);
                    
                    $this->notify('success', 'Configuration définie par défaut avec succès.');
                    $this->redirect($this->getResource()::getUrl('view', ['record' => $this->record->id]));
                }),
            Actions\DeleteAction::make()
                ->before(function () {
                    // Vérifier si c'est la seule configuration de l'entreprise
                    $nbConfigurations = ConfigurationPaie::where('entreprise_id', $this->record->entreprise_id)->count();
                    if ($nbConfigurations <= 1) {
                        $this->halt();
                        $this->notify('danger', 'Vous ne pouvez pas supprimer la seule configuration de paie de votre entreprise.');
                    }
                    
                    // Vérifier si la configuration est utilisée par des bulletins de paie
                    if ($this->record->bulletins()->exists()) {
                        $this->halt();
                        $this->notify('danger', 'Cette configuration de paie est utilisée par des bulletins de paie et ne peut pas être supprimée.');
                    }
                }),
        ];
    }
}
