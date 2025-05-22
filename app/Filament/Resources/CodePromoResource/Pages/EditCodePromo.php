<?php

namespace App\Filament\Resources\CodePromoResource\Pages;

use App\Filament\Resources\CodePromoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCodePromo extends EditRecord
{
    protected static string $resource = CodePromoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
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
            Actions\Action::make('resetUtilisations')
                ->label('Réinitialiser utilisations')
                ->icon('heroicon-o-arrow-path')
                ->color('warning')
                ->requiresConfirmation()
                ->action(function () {
                    $this->record->update(['nombre_utilisations' => 0]);
                    $this->notify('success', 'Le compteur d\'utilisations a été réinitialisé');
                }),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
