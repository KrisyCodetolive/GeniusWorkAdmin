<?php

namespace App\Filament\Resources\TaskRoutineResource\Pages;

use App\Filament\Resources\TaskRoutineResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use App\Services\Tasks\TaskRoutineService;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

class CreateTaskRoutine extends CreateRecord
{
    protected static string $resource = TaskRoutineResource::class;
    
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
    
    protected function handleRecordCreation(array $data): Model
    {
        // Assurons-nous que c'est bien une tâche routinière
        $data['est_routine'] = true;
        
        // Utiliser le service dédié aux tâches routinières
        $taskRoutineService = app(TaskRoutineService::class);
        return $taskRoutineService->creerTacheRoutine($data);
    }
    
    protected function afterCreate(): void
    {
        $task = $this->record;
        
        // Notification de succès
        Notification::make()
            ->title('Tâche routinière créée avec succès')
            ->success()
            ->send();
    }
}
