<?php

namespace App\Filament\Resources\TaskRoutineResource\Pages;

use App\Filament\Resources\TaskRoutineResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use App\Services\Tasks\TaskRoutineService;
use Filament\Notifications\Notification;

class EditTaskRoutine extends EditRecord
{
    protected static string $resource = TaskRoutineResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
            Actions\Action::make('activer')
                ->label('Activer')
                ->icon('heroicon-o-play')
                ->color('success')
                ->action(function () {
                    $this->record->update(['active' => true]);
                    
                    Notification::make()
                        ->title('Routine activée')
                        ->success()
                        ->send();
                })
                ->requiresConfirmation()
                ->visible(fn () => !$this->record->active),
            
            Actions\Action::make('desactiver')
                ->label('Désactiver')
                ->icon('heroicon-o-pause')
                ->color('danger')
                ->action(function () {
                    $this->record->update(['active' => false]);
                    
                    Notification::make()
                        ->title('Routine désactivée')
                        ->success()
                        ->send();
                })
                ->requiresConfirmation()
                ->visible(fn () => $this->record->active),
            
            Actions\Action::make('generer_maintenant')
                ->label('Générer maintenant')
                ->icon('heroicon-o-arrow-path')
                ->color('warning')
                ->action(function () {
                    $taskRoutineService = app(TaskRoutineService::class);
                    $taskRoutineService->genererTacheDepuisRoutine($this->record);
                    
                    Notification::make()
                        ->title('Tâche générée manuellement')
                        ->success()
                        ->send();
                })
                ->requiresConfirmation()
                ->visible(fn () => $this->record->active),
        ];
    }
    
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        // Assurons-nous que c'est bien une tâche routinière
        $data['est_routine'] = true;
        
        // Utiliser le service dédié aux tâches routinières
        $taskRoutineService = app(TaskRoutineService::class);
        return $taskRoutineService->mettreAJourTacheRoutine($record, $data);
    }
    
    protected function afterSave(): void
    {
        Notification::make()
            ->title('Tâche routinière mise à jour avec succès')
            ->success()
            ->send();
    }
}
