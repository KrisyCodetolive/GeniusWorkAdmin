<?php

namespace App\Filament\Resources\RetardAbsenceResource\Pages;

use App\Filament\Resources\RetardAbsenceResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class EditRetardAbsence extends EditRecord
{
    protected static string $resource = RetardAbsenceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ActionGroup::make([
                Actions\Action::make('valider')
                    ->label('Approuver')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn () => $this->record->validateur_id === null)
                    ->requiresConfirmation()
                    ->modalHeading('Approuver cette entrée')
                    ->modalDescription('Confirmez-vous l\'approbation de cette entrée ? Cette action est irréversible.')
                    ->modalSubmitActionLabel('Oui, approuver')
                    ->successNotification(
                        Notification::make()
                            ->success()
                            ->title('Entrée approuvée')
                            ->body('L\'entrée a été approuvée avec succès.')
                    )
                    ->action(function () {
                        $this->record->update([
                            'validateur_id' => Auth::id(),
                            'date_validation' => Carbon::now(),
                            'statut_validation' => 'approve'
                        ]);
                        
                        $this->redirect($this->getResource()::getUrl('index'));
                    }),
                    
                Actions\Action::make('rejeter')
                    ->label('Rejeter')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn () => $this->record->validateur_id === null)
                    ->modalHeading('Rejeter cette entrée')
                    ->modalDescription('Veuillez indiquer le motif du rejet. Cette action est irréversible.')
                    ->modalSubmitActionLabel('Rejeter')
                    ->successNotification(
                        Notification::make()
                            ->warning()
                            ->title('Entrée rejetée')
                            ->body('L\'entrée a été rejetée.')
                    )
                    ->form([
                        \Filament\Forms\Components\Textarea::make('commentaire')
                            ->label('Motif de rejet')
                            ->placeholder('Veuillez indiquer la raison du rejet...')
                            ->required()
                            ->minLength(10)
                            ->helperText('Minimum 10 caractères'),
                    ])
                    ->action(function (array $data) {
                        $this->record->update([
                            'validateur_id' => Auth::id(),
                            'date_validation' => Carbon::now(),
                            'statut_validation' => 'rejete',
                            'commentaire' => $data['commentaire']
                        ]);
                        
                        $this->redirect($this->getResource()::getUrl('index'));
                    }),
            ])
            ->label('Actions')
            ->color('primary')
            ->button(),
            
            Actions\Action::make('retour')
                ->label('Retour à la liste')
                ->url($this->getResource()::getUrl('index'))
                ->color('gray')
                ->icon('heroicon-o-arrow-left'),
        ];
    }
    
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
