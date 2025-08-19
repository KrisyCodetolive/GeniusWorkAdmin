<?php

namespace App\Filament\Resources\TaskRoutineResource\Pages;

use App\Filament\Resources\TaskRoutineResource;
use App\Filament\Actions\ExporterTachesAction;
use App\Filament\Actions\GenererRapportTachesAction;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Task;

class ListTaskRoutines extends ListRecords
{
    protected static string $resource = TaskRoutineResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Actions\Action::make('generer_toutes_routines')
                ->label('Générer toutes les routines')
                ->icon('heroicon-o-arrow-path')
                ->color('warning')
                ->action(function () {
                    $taskRoutineService = app(\App\Services\Tasks\TaskRoutineService::class);
                    $count = $taskRoutineService->genererToutesLesTachesRoutinieres();
                    
                    \Filament\Notifications\Notification::make()
                        ->title($count . ' tâches générées avec succès')
                        ->success()
                        ->send();
                })
                ->requiresConfirmation(),
            ExporterTachesAction::make()
                ->label('Exporter les routines'),
            GenererRapportTachesAction::make()
                ->label('Rapport des routines'),
        ];
    }
    
    public function getTabs(): array
    {
        return [
            'toutes' => Tab::make('Toutes les routines')
                ->badge(Task::where('est_routine', true)->count()),
            'actives' => Tab::make('Actives')
                ->badge(Task::where('est_routine', true)->where('active', true)->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->where('active', true)),
            'inactives' => Tab::make('Inactives')
                ->badge(Task::where('est_routine', true)->where('active', false)->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->where('active', false)),
            'quotidiennes' => Tab::make('Quotidiennes')
                ->badge(Task::where('est_routine', true)->where('frequence_routine', Task::FREQUENCE_QUOTIDIENNE)->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->where('frequence_routine', Task::FREQUENCE_QUOTIDIENNE)),
            'hebdomadaires' => Tab::make('Hebdomadaires')
                ->badge(Task::where('est_routine', true)->where('frequence_routine', Task::FREQUENCE_HEBDOMADAIRE)->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->where('frequence_routine', Task::FREQUENCE_HEBDOMADAIRE)),
            'mensuelles' => Tab::make('Mensuelles')
                ->badge(Task::where('est_routine', true)->where('frequence_routine', Task::FREQUENCE_MENSUELLE)->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->where('frequence_routine', Task::FREQUENCE_MENSUELLE)),
            'autres' => Tab::make('Autres fréquences')
                ->badge(Task::where('est_routine', true)->whereNotIn('frequence_routine', [Task::FREQUENCE_QUOTIDIENNE, Task::FREQUENCE_HEBDOMADAIRE, Task::FREQUENCE_MENSUELLE])->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->whereNotIn('frequence_routine', [Task::FREQUENCE_QUOTIDIENNE, Task::FREQUENCE_HEBDOMADAIRE, Task::FREQUENCE_MENSUELLE])),
        ];
    }
}
