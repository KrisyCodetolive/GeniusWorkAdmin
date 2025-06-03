<?php

namespace App\Filament\Resources\VisiteResource\Pages;

use App\Filament\Resources\VisiteResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Resources\VisiteurResource;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Visite;
use Illuminate\Support\Facades\Auth;
use Filament\Forms;

class ListVisites extends ListRecords
{
    protected static string $resource = VisiteResource::class;

    protected function getHeaderActions(): array
    {
        return [

            Actions\CreateAction::make()
                ->label('Visites illimitées')
                ->icon('heroicon-o-sparkles')
                ->color('success')
                ->tooltip("Vous pouvez créer un nombre illimité de visites")
                ->visible(fn (): bool => auth()->user()->isAdmin() || auth()->user()->isSuperAdmin() || auth()->user()->isSupport()),

            Actions\CreateAction::make()
                ->label('Nouvelle visite')
                ->icon('heroicon-o-plus-circle'),
            Actions\Action::make('Visiteurs')
                ->label('Gestion des visiteurs')
                ->icon('heroicon-o-user-group')
                ->color('success')
                ->url(fn (): string => VisiteurResource::getUrl())
                ->visible(fn (): bool => auth()->user()->isAdmin() || auth()->user()->isSuperAdmin() || auth()->user()->isSupport()),
            Actions\Action::make('stats')
                ->label('Statistiques')
                ->icon('heroicon-o-chart-bar')
                ->color('primary')
                ->url(fn (): string => VisiteResource::getUrl('stats'))
                ->visible(fn (): bool => auth()->user()->isAdmin() || auth()->user()->isSuperAdmin() || auth()->user()->isSupport()),
            Actions\Action::make('rapport')
                ->label('Générer un rapport')
                ->icon('heroicon-o-document-text')
                ->color('warning')
                ->form([
                    Forms\Components\DatePicker::make('date_debut')
                        ->label('Date de début')
                        ->required()
                        ->default(now()->startOfMonth()),
                    Forms\Components\DatePicker::make('date_fin')
                        ->label('Date de fin')
                        ->required()
                        ->default(now()),
                    Forms\Components\Select::make('site_id')
                        ->label('Site')
                        ->options(function () {
                            $user = auth()->user();
                            $query = \App\Models\Site::query();
                            
                            if (!$user->isSuperAdmin() && !$user->isSupport()) {
                                $query->where('entreprise_id', $user->entreprise_id);
                            }
                            
                            $options = $query->pluck('nom', 'id')->toArray();
                            return ['' => 'Tous les sites'] + $options;
                        })
                        ->placeholder('Tous les sites')
                        ->searchable(),
                ])
                ->action(function (array $data): void {
                    $url = route('visites.rapport', [
                        'date_debut' => $data['date_debut'],
                        'date_fin' => $data['date_fin'],
                        'site_id' => $data['site_id'] ?? null,
                    ]);
                    
                    redirect()->to($url);
                })
                ->visible(fn (): bool => auth()->user()->isAdmin() || auth()->user()->isSuperAdmin() || auth()->user()->isSupport()),
        ];
    }
    
    public function getTabs(): array
    {
        return [
            'toutes' => Tab::make('Toutes les visites')
                ->badge(function () {
                    $query = Visite::query();
                    if (Auth::user() && Auth::user()->entreprise_id) {
                        $query->where('entreprise_id', Auth::user()->entreprise_id);
                    }
                    return $query->count();
                }),
            'en_cours' => Tab::make('En cours')
                ->badge(function () {
                    $query = Visite::where('statut', 'en_cours');
                    if (Auth::user() && Auth::user()->entreprise_id) {
                        $query->where('entreprise_id', Auth::user()->entreprise_id);
                    }
                    return $query->count();
                })
                ->modifyQueryUsing(fn (Builder $query) => $query->where('statut', 'en_cours')),
            'aujourd_hui' => Tab::make('Aujourd\'hui')
                ->badge(function () {
                    $query = Visite::whereDate('date_arrivee', today());
                    if (Auth::user() && Auth::user()->entreprise_id) {
                        $query->where('entreprise_id', Auth::user()->entreprise_id);
                    }
                    return $query->count();
                })
                ->modifyQueryUsing(fn (Builder $query) => $query->whereDate('date_arrivee', today())),
            'cette_semaine' => Tab::make('Cette semaine')
                ->badge(function () {
                    $query = Visite::whereBetween('date_arrivee', [now()->startOfWeek(), now()->endOfWeek()]);
                    if (Auth::user() && Auth::user()->entreprise_id) {
                        $query->where('entreprise_id', Auth::user()->entreprise_id);
                    }
                    return $query->count();
                })
                ->modifyQueryUsing(fn (Builder $query) => $query->whereBetween('date_arrivee', [now()->startOfWeek(), now()->endOfWeek()])),
            'terminees' => Tab::make('Terminées')
                ->badge(function () {
                    $query = Visite::where('statut', 'terminee');
                    if (Auth::user() && Auth::user()->entreprise_id) {
                        $query->where('entreprise_id', Auth::user()->entreprise_id);
                    }
                    return $query->count();
                })
                ->modifyQueryUsing(fn (Builder $query) => $query->where('statut', 'terminee')),
            'annulees' => Tab::make('Annulées')
                ->badge(function () {
                    $query = Visite::where('statut', 'annulee');
                    if (Auth::user() && Auth::user()->entreprise_id) {
                        $query->where('entreprise_id', Auth::user()->entreprise_id);
                    }
                    return $query->count();
                })
                ->modifyQueryUsing(fn (Builder $query) => $query->where('statut', 'annulee')),
        ];
    }
    
    public function getDefaultActiveTab(): string | int | null
    {
        return 'en_cours';
    }
    
    protected function getTableQuery(): Builder
    {
        $query = parent::getTableQuery();
        
        // Si l'utilisateur est associé à une entreprise, filtrer les visites par entreprise
        $user = Auth::user();
        if ($user && $user->entreprise_id) {
            $query->where('entreprise_id', $user->entreprise_id);
        }
        
        // Appliquer le filtre par site s'il est présent dans la requête
        if (request()->has('site')) {
            $siteId = request()->get('site');
            $query->where('site_id', $siteId);
        }
        
        return $query;
    }
}
