<?php

namespace App\Filament\Resources\RapportResource\Pages;

use App\Filament\Resources\RapportResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRapport extends EditRecord
{
    protected static string $resource = RapportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
            Actions\Action::make('telecharger')
                ->label('Télécharger')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->url(fn () => route('rapports.telecharger', ['rapport' => $this->record]))
                ->openUrlInNewTab(),
        ];
    }
}
