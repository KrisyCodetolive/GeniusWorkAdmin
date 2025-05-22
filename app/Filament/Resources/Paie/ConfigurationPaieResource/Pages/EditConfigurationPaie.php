<?php

namespace App\Filament\Resources\Paie\ConfigurationPaieResource\Pages;

use App\Filament\Resources\Paie\ConfigurationPaieResource;
use App\Models\Paie\ConfigurationPaie;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditConfigurationPaie extends EditRecord
{
    protected static string $resource = ConfigurationPaieResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
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
    
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->record]);
    }
    
    protected function afterSave(): void
    {
        // Si cette configuration est définie comme par défaut, désactiver les autres configurations par défaut
        if ($this->record->est_defaut) {
            ConfigurationPaie::where('entreprise_id', $this->record->entreprise_id)
                ->where('id', '!=', $this->record->id)
                ->where('est_defaut', true)
                ->update(['est_defaut' => false]);
        }
    }
}
