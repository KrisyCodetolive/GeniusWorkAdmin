<?php

namespace App\Filament\Resources\TaskResource\Pages;

use App\Filament\Resources\TaskResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use App\Services\Tasks\TaskService;
use App\Services\Tasks\TaskRoutineService;
use Filament\Notifications\Notification;

class EditTask extends EditRecord
{
    protected static string $resource = TaskResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
            Actions\Action::make('marquer_terminee')
                ->label('Marquer comme terminée')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->action(function () {
                    $this->record->marquerCommeTermine();
                    
                    Notification::make()
                        ->title('Tâche marquée comme terminée')
                        ->success()
                        ->send();
                        
                    $this->redirect($this->getResource()::getUrl('view', ['record' => $this->record]));
                })
                ->requiresConfirmation()
                ->visible(fn () => $this->record->statut !== \App\Models\Task::STATUT_TERMINE && $this->record->statut !== \App\Models\Task::STATUT_ANNULE),
        ];
    }
    
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        // Si c'est une tâche de routine, utiliser le service approprié
        if ($data['est_routine'] ?? false) {
            $taskRoutineService = app(TaskRoutineService::class);
            return $taskRoutineService->mettreAJourTacheRoutine($record, $data);
        }
        
        // Sinon, mettre à jour la tâche standard
        $record->update($data);
        return $record;
    }
    
    protected function afterSave(): void
    {
        Notification::make()
            ->title('Tâche mise à jour avec succès')
            ->success()
            ->send();
    }
}
