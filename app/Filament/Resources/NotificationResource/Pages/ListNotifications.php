<?php

namespace App\Filament\Resources\NotificationResource\Pages;

use App\Filament\Resources\NotificationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListNotifications extends ListRecords
{
    protected static string $resource = NotificationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Actions\Action::make('marquerToutesCommeLues')
                ->label('Marquer toutes comme lues')
                ->icon('heroicon-o-check')
                ->color('success')
                ->action(function () {
                    $user = auth()->user();
                    $query = \App\Models\Notification::query()
                        ->nonLues();
                    
                    // Si l'utilisateur n'est pas administrateur, filtrer par utilisateur
                    if (!$user->hasRole('admin')) {
                        $query->where('user_id', $user->id);
                    }
                    
                    $count = $query->count();
                    
                    $query->each(function ($notification) {
                        $notification->marquerCommeLue();
                    });
                    
                    $this->notify('success', $count . ' notifications marquées comme lues');
                }),
        ];
    }

    protected function getTableQuery(): Builder
    {
        $query = parent::getTableQuery();
        
        // Si l'utilisateur n'est pas administrateur, filtrer par utilisateur
        $user = auth()->user();
        if (!$user->hasRole('admin')) {
            $query->where('user_id', $user->id);
        }
        
        return $query;
    }
}
