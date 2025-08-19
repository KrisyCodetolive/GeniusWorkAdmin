<?php

namespace App\Filament\Resources\TaskResource\Pages;

use App\Filament\Resources\TaskResource;
use App\Filament\Actions\ExporterTachesAction;
use App\Filament\Actions\GenererRapportTachesAction;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Task;

class ListTasks extends ListRecords
{
    protected static string $resource = TaskResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            ExporterTachesAction::make(),
            GenererRapportTachesAction::make(),
        ];
    }
    
    public function getTabs(): array
    {
        return [
            'toutes' => Tab::make('Toutes les tâches')
                ->badge(Task::count()),
            'en_attente' => Tab::make('En attente')
                ->badge(Task::where('statut', Task::STATUT_EN_ATTENTE)->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->where('statut', Task::STATUT_EN_ATTENTE)),
            'en_cours' => Tab::make('En cours')
                ->badge(Task::where('statut', Task::STATUT_EN_COURS)->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->where('statut', Task::STATUT_EN_COURS)),
            'terminees' => Tab::make('Terminées')
                ->badge(Task::where('statut', Task::STATUT_TERMINE)->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->where('statut', Task::STATUT_TERMINE)),
            'en_retard' => Tab::make('En retard')
                ->badge(Task::where('statut', Task::STATUT_EN_RETARD)->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->where('statut', Task::STATUT_EN_RETARD)),
            'annulees' => Tab::make('Annulées')
                ->badge(Task::where('statut', Task::STATUT_ANNULE)->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->where('statut', Task::STATUT_ANNULE)),
            'routines' => Tab::make('Routines')
                ->badge(Task::where('est_routine', true)->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->where('est_routine', true)),
        ];
    }
}
