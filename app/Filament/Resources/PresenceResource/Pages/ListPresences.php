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
        $user = auth()->user();
        $isAdmin = $user->isAdmin() || $user->isSuperAdmin() || $user->isSupport();
        
        return [
            // Action principale de création
            Actions\CreateAction::make()
                ->label('Nouvelle présence')
                ->icon('heroicon-o-plus'),
                
            // Menu déroulant pour les outils de pointage
            Actions\ActionGroup::make([
                // SmartClock pour pointage physique
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
                    
                // Scanner QR Code mobile
                Actions\Action::make('scannerQRCode')
                    ->label('Scanner QR Code Mobile')
                    ->icon('heroicon-o-device-phone-mobile')
                    ->url(route('mobile.pointage.scanner'))
                    ->openUrlInNewTab(),
                    
                // Télécharger App Mobile
                Actions\Action::make('downloadMobileApp')
                    ->label('Télécharger App Mobile')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('primary')
                    ->extraAttributes([
                        'x-on:click' => "window.dispatchEvent(new CustomEvent('open-modal', { detail: { id: 'download-mobile-app-modal' } }))",
                    ])
                    ->modalHeading('Application Mobile Genius Work')
                    ->modalDescription('L\'application mobile Genius Work est en cours de déploiement sur Play Store et App Store. En attendant, vous pouvez télécharger directement l\'APK pour Android.')
            ])
            ->label('Outils de pointage')
            ->icon('heroicon-o-finger-print')
            ->visible($isAdmin),
            
            // Menu déroulant pour les rapports
            Actions\ActionGroup::make([
                // Rapport de présence
                GeneratePresenceReportAction::make()
                    ->label('Rapport de présence'),
                    
                // Rapport d'heures de travail (commenté)
                // GenerateHeuresTravailReportAction::make()
                //    ->label('Rapport d\'heures de travail'),
                    
                // Importer et analyser des présences
                ImporterAnalyserPresenceAction::make()
                    ->label('Importer et analyser'),
            ])
            ->label('Rapports')
            ->icon('heroicon-o-document-chart-bar')
            ->visible($isAdmin),
            
            // Données d'exemple (visible uniquement pour les entreprises sans présences)
            \App\Filament\Actions\GeneratePresencesExemplesAction::make()
                ->label('Générer des exemples')
                ->icon('heroicon-o-beaker')
                ->visible(function () use ($user) {
                    // Vérifier si l'utilisateur a les droits nécessaires
                    if (!$user->isAdmin()) {
                        return false;
                    }
                    
                    $entreprise = $user->entreprise;
                    if (!$entreprise) {
                        return false;
                    }
                    
                    // Vérifier si l'entreprise a déjà des présences
                    $existingPresences = \App\Models\Presence::whereHas('employeur', function($query) use ($entreprise) {
                        $query->where('entreprise_id', $entreprise->id);
                    })->count();
                    
                    // Ne montrer l'action que si l'entreprise n'a pas encore de présences
                    return $existingPresences === 0;
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
