<?php

namespace App\Filament\Resources\TaskResource\Pages;

use App\Filament\Resources\TaskResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use App\Services\Tasks\TaskService;
use App\Services\Tasks\TaskRoutineService;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

class CreateTask extends CreateRecord
{
    protected static string $resource = TaskResource::class;
    
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
    
    protected function handleRecordCreation(array $data): Model
    {
        // Si c'est une tâche de routine, utiliser le service approprié
        if ($data['est_routine'] ?? false) {
            $taskRoutineService = app(TaskRoutineService::class);
            return $taskRoutineService->creerTacheRoutine($data);
        }
        
        // Sinon, créer une tâche standard
        $taskService = app(TaskService::class);
        return $taskService->creerTask($data);
    }
    
    protected function afterCreate(): void
    {
        $task = $this->record;
        
        // Notification de succès
        Notification::make()
            ->title('Tâche créée avec succès')
            ->success()
            ->send();
    }
}
