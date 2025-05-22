<?php

namespace App\Filament\Resources\PolitiqueResource\Pages;

use App\Filament\Resources\PolitiqueResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewPolitique extends ViewRecord
{
    protected static string $resource = PolitiqueResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\Action::make('duplicate')
                ->label('Dupliquer')
                ->icon('heroicon-o-document-duplicate')
                ->color('gray')
                ->action(function () {
                    $politique = $this->record;
                    $newPolitique = $politique->replicate();
                    $newPolitique->save();
                    
                    $this->notify('success', 'La politique a été dupliquée avec succès');
                    return redirect()->to(PolitiqueResource::getUrl('edit', ['record' => $newPolitique]));
                }),
        ];
    }
}
