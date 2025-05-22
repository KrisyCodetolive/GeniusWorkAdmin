<?php

namespace App\Filament\Resources\RetardAbsenceResource\Pages;

use App\Filament\Resources\RetardAbsenceResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class EditRetardAbsence extends EditRecord
{
    protected static string $resource = RetardAbsenceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('valider')
                ->label('Approuver')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->visible(fn () => $this->record->validateur_id === null)
                ->action(function () {
                    $this->record->update([
                        'validateur_id' => Auth::id(),
                        'date_validation' => Carbon::now(),
                        'statut_validation' => 'approve'
                    ]);
                    
                    $typeEvent = $this->record->statut === 'retard' ? 'Retard' : 'Absence';
                    
                    $this->notification()->success(
                        $typeEvent . ' approuvé',
                        "Le " . strtolower($typeEvent) . " a été approuvé avec succès."
                    );
                    
                    $this->redirect($this->getResource()::getUrl('index'));
                }),
                
            Actions\Action::make('rejeter')
                ->label('Rejeter')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->visible(fn () => $this->record->validateur_id === null)
                ->form([
                    \Filament\Forms\Components\Textarea::make('commentaire')
                        ->label('Motif de rejet')
                        ->required(),
                ])
                ->action(function (array $data) {
                    $this->record->update([
                        'validateur_id' => Auth::id(),
                        'date_validation' => Carbon::now(),
                        'statut_validation' => 'rejete',
                        'commentaire' => $data['commentaire']
                    ]);
                    
                    $typeEvent = $this->record->statut === 'retard' ? 'Retard' : 'Absence';
                    
                    $this->notification()->success(
                        $typeEvent . ' rejeté',
                        "Le " . strtolower($typeEvent) . " a été rejeté avec succès."
                    );
                    
                    $this->redirect($this->getResource()::getUrl('index'));
                }),
        ];
    }
    
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
