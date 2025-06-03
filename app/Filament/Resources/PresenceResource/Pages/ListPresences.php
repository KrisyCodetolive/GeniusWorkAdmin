<?php

namespace App\Filament\Resources\PresenceResource\Pages;

use App\Filament\Resources\PresenceResource;
use App\Filament\Resources\RetardAbsenceResource;
use App\Filament\Actions\GeneratePresenceReportAction;
use App\Filament\Actions\GenerateHeuresTravailReportAction;
use App\Filament\Actions\ImporterAnalyserPresenceAction;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use App\Models\Site;

class ListPresences extends ListRecords
{
    protected static string $resource = PresenceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Nouvelle présence')
                ->icon('heroicon-o-plus'),
                
            \App\Filament\Actions\GeneratePresencesExemplesAction::make()
                ->visible(function () {
                    $user = auth()->user();
                    
                    // Vérifier si l'utilisateur a les droits nécessaires
                    if (!$user->isAdmin()) {
                        return false;
                    }
                    
                    $entreprise = $user->entreprise;
                    if (!$entreprise) {
                        return false;
                    }
                    
                    // Vérifier si l'entreprise a déjà des présences
                    // La table Presence n'a pas de colonne entreprise_id directe
                    // On doit passer par la relation avec les employeurs
                    $existingPresences = \App\Models\Presence::whereHas('employeur', function($query) use ($entreprise) {
                        $query->where('entreprise_id', $entreprise->id);
                    })->count();
                    
                    // Ne montrer l'action que si l'entreprise n'a pas encore de présences
                    return $existingPresences === 0;
                }),
                
            GeneratePresenceReportAction::make()
                ->visible(fn (): bool => auth()->user()->isAdmin() || auth()->user()->isSuperAdmin() || auth()->user()->isSupport()),
                
         //   GenerateHeuresTravailReportAction::make()
         //       ->visible(fn (): bool => auth()->user()->isAdmin() || auth()->user()->isSuperAdmin() || auth()->user()->isSupport()),
                
            ImporterAnalyserPresenceAction::make()
                ->visible(fn (): bool => auth()->user()->isAdmin() || auth()->user()->isSuperAdmin() || auth()->user()->isSupport()),
                
         
            \Filament\Actions\Action::make('smartClock')
                ->label('SmartClock')
                ->icon('heroicon-o-qr-code')
                ->color('success')
                ->form([
                    \Filament\Forms\Components\Select::make('site_id')
                        ->label('Sélectionnez un site')
                        ->options(function () {
                            $user = auth()->user();
                            $query = \App\Models\Site::query();
                            
                            if (!$user->isSuperAdmin() && !$user->isSupport()) {
                                $query->where('entreprise_id', $user->entreprise_id);
                            }
                            
                            return $query->pluck('nom', 'id');
                        })
                        ->searchable()
                        ->preload()
                        ->required()
                ])
                ->action(function (array $data) {
                    $site = \App\Models\Site::find($data['site_id']);
                    if (!$site) {
                        \Filament\Notifications\Notification::make()
                            ->title('Erreur')
                            ->body('Site non trouvé')
                            ->danger()
                            ->send();
                        return;
                    }
                    
                    // Stocker le site sélectionné en session
                    session(['selected_site_id' => $site->id]);
                    
                    // Rediriger vers la page SmartClock
                    return redirect()->route('smart-clock.index');
                }),

                
        ];
    }
    
    public function getTabs(): array
    {
        $today = Carbon::today()->toDateString();
        $startOfWeek = Carbon::now()->startOfWeek()->toDateString();
        $endOfWeek = Carbon::now()->endOfWeek()->toDateString();
        
        return [
            'tous' => Tab::make('Tous')
                ->badge(fn () => $this->getTableQuery()->count()),
            'aujourd_hui' => Tab::make('Aujourd\'hui')
                ->badge(fn () => $this->getTableQuery()->where(function (Builder $query) use ($today) {
                    $query->whereDate('date_heure_entree', $today)
                        ->orWhereDate('date_heure_sortie', $today);
                })->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->where(function (Builder $query) use ($today) {
                    $query->whereDate('date_heure_entree', $today)
                        ->orWhereDate('date_heure_sortie', $today);
                })),
            'cette_semaine' => Tab::make('Cette semaine')
                ->badge(fn () => $this->getTableQuery()->where(function (Builder $query) use ($startOfWeek, $endOfWeek) {
                    $query->whereBetween('date_heure_entree', [$startOfWeek, $endOfWeek])
                        ->orWhereBetween('date_heure_sortie', [$startOfWeek, $endOfWeek]);
                })->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->where(function (Builder $query) use ($startOfWeek, $endOfWeek) {
                    $query->whereBetween('date_heure_entree', [$startOfWeek, $endOfWeek])
                        ->orWhereBetween('date_heure_sortie', [$startOfWeek, $endOfWeek]);
                })),
            'entrees' => Tab::make('Entrées')
                ->badge(fn () => $this->getTableQuery()->whereNotNull('date_heure_entree')->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->whereNotNull('date_heure_entree')),
            'sorties' => Tab::make('Sorties')
                ->badge(fn () => $this->getTableQuery()->whereNotNull('date_heure_sortie')->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->whereNotNull('date_heure_sortie')),
            'non_valides' => Tab::make('Non validés')
                ->badge(fn () => $this->getTableQuery()->whereNull('validateur_id')->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->whereNull('validateur_id')),
        ];
    }
}
