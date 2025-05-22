<?php

namespace App\Filament\Resources\CodePromoResource\Pages;

use App\Filament\Resources\CodePromoResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewCodePromo extends ViewRecord
{
    protected static string $resource = CodePromoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\Action::make('duplicate')
                ->label('Dupliquer')
                ->icon('heroicon-o-document-duplicate')
                ->color('gray')
                ->action(function () {
                    $record = $this->record;
                    $newCode = $record->replicate();
                    $newCode->code = $record->code . '-COPY';
                    $newCode->nombre_utilisations = 0;
                    $newCode->save();
                    
                    return redirect()->route('filament.admin.resources.code-promos.edit', ['record' => $newCode->id]);
                }),
            Actions\Action::make('toggleActif')
                ->label(fn ($record) => $record->actif ? 'Désactiver' : 'Activer')
                ->icon(fn ($record) => $record->actif ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle')
                ->color(fn ($record) => $record->actif ? 'danger' : 'success')
                ->action(function () {
                    $this->record->update(['actif' => !$this->record->actif]);
                    $this->notify('success', $this->record->actif ? 'Code promo activé' : 'Code promo désactivé');
                }),
        ];
    }
}
