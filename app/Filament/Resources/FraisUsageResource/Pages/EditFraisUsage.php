<?php

namespace App\Filament\Resources\FraisUsageResource\Pages;

use App\Filament\Resources\FraisUsageResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditFraisUsage extends EditRecord
{
    protected static string $resource = FraisUsageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
            Actions\Action::make('facturer')
                ->label('Facturer')
                ->icon('heroicon-o-document-text')
                ->color('success')
                ->visible(fn () => $this->record->facturation_id === null)
                ->url(fn () => route('filament.admin.resources.facturations.create', [
                    'frais_ids' => [$this->record->id],
                ])),
            Actions\Action::make('recalculer')
                ->label('Recalculer le montant')
                ->icon('heroicon-o-calculator')
                ->color('warning')
                ->action(function () {
                    $this->record->calculerMontantTotal();
                    $this->notify('success', 'Le montant total a été recalculé');
                }),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Calculer le montant total si ce n'est pas déjà fait
        if (empty($data['montant_total']) && !empty($data['prix_unitaire']) && !empty($data['quantite'])) {
            $data['montant_total'] = $data['prix_unitaire'] * $data['quantite'];
        }
        
        return $data;
    }
}
