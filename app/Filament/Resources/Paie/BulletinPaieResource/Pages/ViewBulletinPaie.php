<?php

namespace App\Filament\Resources\Paie\BulletinPaieResource\Pages;

use App\Filament\Resources\Paie\BulletinPaieResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;

class ViewBulletinPaie extends ViewRecord
{
    protected static string $resource = BulletinPaieResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->visible(fn () => $this->record->statut === 'brouillon'),
                
            Actions\Action::make('pdf')
                ->label('Télécharger PDF')
                ->icon('heroicon-o-document-arrow-down')
                ->url(fn () => route('paie.bulletins.pdf', $this->record->id))
                ->openUrlInNewTab(),

            Actions\Action::make('visualiser')
                ->label('Visualiser')
                ->icon('heroicon-o-eye')
                ->url(fn ($record): string => route('paie.bulletins.tailwind', $record->id))
                ->openUrlInNewTab(),
                
            Actions\Action::make('valider')
                ->label('Valider')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->visible(fn () => $this->record->statut === 'brouillon')
                ->action(function () {
                    $this->record->update([
                        'statut' => 'validé',
                        'valide_par' => Auth::id(),
                        'date_validation' => now(),
                    ]);
                    
                    Notification::make()
                        ->title('Bulletin validé')
                        ->success()
                        ->send();
                    
                    $this->redirect($this->getResource()::getUrl('view', ['record' => $this->record->id]));
                }),
                
            Actions\Action::make('annuler')
                ->label('Annuler')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->requiresConfirmation()
                ->visible(fn () => $this->record->statut !== 'annulé')
                ->form([
                    \Filament\Forms\Components\Textarea::make('commentaire')
                        ->label('Motif d\'annulation')
                        ->required(),
                ])
                ->action(function (array $data) {
                    $this->record->update([
                        'statut' => 'annulé',
                        'commentaire' => $data['commentaire'],
                    ]);
                    
                    Notification::make()
                        ->title('Bulletin annulé')
                        ->danger()
                        ->send();
                    
                    $this->redirect($this->getResource()::getUrl('view', ['record' => $this->record->id]));
                }),
        ];
    }
}
