<?php

namespace App\Filament\Resources\RapportResource\Pages;

use App\Filament\Resources\RapportResource;
use Filament\Resources\Pages\Page;
use App\Models\Presence;
use App\Models\User;
use App\Models\Employeur;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;

class Tableaux extends Page
{
    protected static string $resource = RapportResource::class;

    protected static string $view = 'filament.resources.rapport-resource.pages.tableaux';

    protected static ?string $navigationIcon = 'heroicon-o-presentation-chart-line';

    protected static ?string $navigationLabel = 'Tableaux de bord';

    protected static ?int $navigationSort = 1;

    public $selectedEmployeur = null;
    public $periode = 'semaine';
    public $dateDebut;
    public $dateFin;
    public $activeTab = 'presence';

    public function mount()
    {
        $this->dateDebut = Carbon::now()->startOfWeek();
        $this->dateFin = Carbon::now()->endOfWeek();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('filtrer')
                ->label('Filtrer')
                ->icon('heroicon-o-funnel')
                ->form([
                    Select::make('employeur')
                        ->label('Employeur')
                        ->options(Employeur::pluck('nom', 'id'))
                        ->placeholder('Tous les employeurs')
                        ->live(),
                    Select::make('periode')
                        ->label('Période')
                        ->options([
                            'jour' => 'Aujourd\'hui',
                            'semaine' => 'Cette semaine',
                            'mois' => 'Ce mois',
                            'trimestre' => 'Ce trimestre',
                            'annee' => 'Cette année',
                            'personnalise' => 'Personnalisée',
                        ])
                        ->default('semaine')
                        ->live()
                        ->afterStateUpdated(function ($state, callable $set) {
                            $now = Carbon::now();
                            
                            switch ($state) {
                                case 'jour':
                                    $set('dateDebut', $now->copy()->startOfDay());
                                    $set('dateFin', $now->copy()->endOfDay());
                                    break;
                                case 'semaine':
                                    $set('dateDebut', $now->copy()->startOfWeek());
                                    $set('dateFin', $now->copy()->endOfWeek());
                                    break;
                                case 'mois':
                                    $set('dateDebut', $now->copy()->startOfMonth());
                                    $set('dateFin', $now->copy()->endOfMonth());
                                    break;
                                case 'trimestre':
                                    $set('dateDebut', $now->copy()->startOfQuarter());
                                    $set('dateFin', $now->copy()->endOfQuarter());
                                    break;
                                case 'annee':
                                    $set('dateDebut', $now->copy()->startOfYear());
                                    $set('dateFin', $now->copy()->endOfYear());
                                    break;
                            }
                        }),
                    DatePicker::make('dateDebut')
                        ->label('Date de début')
                        ->visible(fn ($get) => $get('periode') === 'personnalise'),
                    DatePicker::make('dateFin')
                        ->label('Date de fin')
                        ->visible(fn ($get) => $get('periode') === 'personnalise')
                        ->after('dateDebut'),
                ])
                ->action(function (array $data): void {
                    $this->selectedEmployeur = $data['employeur'] ?? null;
                    $this->periode = $data['periode'];
                    
                    if ($this->periode === 'personnalise') {
                        $this->dateDebut = Carbon::parse($data['dateDebut']);
                        $this->dateFin = Carbon::parse($data['dateFin']);
                    } else {
                        $now = Carbon::now();
                        
                        switch ($this->periode) {
                            case 'jour':
                                $this->dateDebut = $now->copy()->startOfDay();
                                $this->dateFin = $now->copy()->endOfDay();
                                break;
                            case 'semaine':
                                $this->dateDebut = $now->copy()->startOfWeek();
                                $this->dateFin = $now->copy()->endOfWeek();
                                break;
                            case 'mois':
                                $this->dateDebut = $now->copy()->startOfMonth();
                                $this->dateFin = $now->copy()->endOfMonth();
                                break;
                            case 'trimestre':
                                $this->dateDebut = $now->copy()->startOfQuarter();
                                $this->dateFin = $now->copy()->endOfQuarter();
                                break;
                            case 'annee':
                                $this->dateDebut = $now->copy()->startOfYear();
                                $this->dateFin = $now->copy()->endOfYear();
                                break;
                        }
                    }
                }),
        ];
    }

    public function getPresenceStats()
    {
        $query = Presence::query()
            ->whereBetween('date_pointage', [$this->dateDebut, $this->dateFin]);
            
        if ($this->selectedEmployeur) {
            $query->where('employeur_id', $this->selectedEmployeur);
        }
        
        $presences = $query->get();
        
        // Total des présences
        $totalPresences = $presences->count();
        
        // Présences par type
        $presencesParType = $presences->groupBy('type_pointage')->map->count();
        
        // Présences par jour
        $presencesParJour = $presences
            ->groupBy(function ($presence) {
                return Carbon::parse($presence->date_pointage)->format('Y-m-d');
            })
            ->map->count();
        
        // Présences par employeur
        $presencesParEmployeur = $presences
            ->groupBy('employeur_id')
            ->map(function ($items) {
                $employeur = Employeur::find($items->first()->employeur_id);
                return [
                    'nom' => $employeur ? $employeur->nom : 'Inconnu',
                    'count' => $items->count(),
                ];
            });
        
        // Taux de présence
        $totalUtilisateurs = User::count();
        $tauxPresence = $totalUtilisateurs > 0 ? ($totalPresences / $totalUtilisateurs) * 100 : 0;
        
        return [
            'total' => $totalPresences,
            'par_type' => $presencesParType,
            'par_jour' => $presencesParJour,
            'par_employeur' => $presencesParEmployeur,
            'taux_presence' => $tauxPresence,
        ];
    }

    public function getRetardAbsenceStats()
    {
        $query = Presence::query()
            ->whereBetween('date_pointage', [$this->dateDebut, $this->dateFin]);
            
        if ($this->selectedEmployeur) {
            $query->where('employeur_id', $this->selectedEmployeur);
        }
        
        $presences = $query->get();
        
        // Retards
        $retards = $presences->where('est_retard', true);
        $totalRetards = $retards->count();
        
        // Absences (utilisateurs qui n'ont pas pointé)
        $utilisateursPresents = $presences->pluck('user_id')->unique();
        $totalUtilisateurs = User::count();
        $totalAbsences = $totalUtilisateurs - $utilisateursPresents->count();
        
        // Retards par jour
        $retardsParJour = $retards
            ->groupBy(function ($presence) {
                return Carbon::parse($presence->date_pointage)->format('Y-m-d');
            })
            ->map->count();
        
        // Retards par employeur
        $retardsParEmployeur = $retards
            ->groupBy('employeur_id')
            ->map(function ($items) {
                $employeur = Employeur::find($items->first()->employeur_id);
                return [
                    'nom' => $employeur ? $employeur->nom : 'Inconnu',
                    'count' => $items->count(),
                ];
            });
        
        // Durée moyenne des retards
        $dureeRetards = $retards->sum('minutes_retard');
        $moyenneRetards = $totalRetards > 0 ? $dureeRetards / $totalRetards : 0;
        
        return [
            'total_retards' => $totalRetards,
            'total_absences' => $totalAbsences,
            'retards_par_jour' => $retardsParJour,
            'retards_par_employeur' => $retardsParEmployeur,
            'duree_moyenne_retard' => $moyenneRetards,
        ];
    }

    public function getHeuresSupplementairesStats()
    {
        $query = DB::table('heures_supplementaires')
            ->whereBetween('date', [$this->dateDebut, $this->dateFin]);
            
        if ($this->selectedEmployeur) {
            $query->where('employeur_id', $this->selectedEmployeur);
        }
        
        $heuresSupp = $query->get();
        
        // Total des heures supplémentaires
        $totalHeuresSupp = $heuresSupp->sum('duree');
        
        // Heures supplémentaires par jour
        $heuresSuppParJour = $heuresSupp
            ->groupBy('date')
            ->map(function ($items) {
                return $items->sum('duree');
            });
        
        // Heures supplémentaires par employeur
        $heuresSuppParEmployeur = $heuresSupp
            ->groupBy('employeur_id')
            ->map(function ($items) {
                $employeur = Employeur::find($items->first()->employeur_id);
                return [
                    'nom' => $employeur ? $employeur->nom : 'Inconnu',
                    'total' => $items->sum('duree'),
                ];
            });
        
        // Heures supplémentaires par utilisateur
        $heuresSuppParUtilisateur = $heuresSupp
            ->groupBy('user_id')
            ->map(function ($items) {
                $user = User::find($items->first()->user_id);
                return [
                    'nom' => $user ? $user->name : 'Inconnu',
                    'total' => $items->sum('duree'),
                ];
            })
            ->sortByDesc('total')
            ->take(10);
        
        return [
            'total' => $totalHeuresSupp,
            'par_jour' => $heuresSuppParJour,
            'par_employeur' => $heuresSuppParEmployeur,
            'par_utilisateur' => $heuresSuppParUtilisateur,
        ];
    }

    public function getCongesStats()
    {
        $query = DB::table('conges')
            ->whereBetween('date_debut', [$this->dateDebut, $this->dateFin])
            ->orWhereBetween('date_fin', [$this->dateDebut, $this->dateFin]);
            
        if ($this->selectedEmployeur) {
            $query->where('employeur_id', $this->selectedEmployeur);
        }
        
        $conges = $query->get();
        
        // Total des congés
        $totalConges = $conges->count();
        
        // Congés par type
        $congesParType = $conges
            ->groupBy('type_conge')
            ->map->count();
        
        // Congés par statut
        $congesParStatut = $conges
            ->groupBy('statut')
            ->map->count();
        
        // Durée moyenne des congés
        $dureeConges = $conges->sum(function ($conge) {
            $debut = Carbon::parse($conge->date_debut);
            $fin = Carbon::parse($conge->date_fin);
            return $debut->diffInDays($fin) + 1;
        });
        
        $moyenneConges = $totalConges > 0 ? $dureeConges / $totalConges : 0;
        
        // Congés par mois
        $congesParMois = $conges
            ->groupBy(function ($conge) {
                return Carbon::parse($conge->date_debut)->format('Y-m');
            })
            ->map->count();
        
        return [
            'total' => $totalConges,
            'par_type' => $congesParType,
            'par_statut' => $congesParStatut,
            'duree_moyenne' => $moyenneConges,
            'par_mois' => $congesParMois,
        ];
    }

    public function setActiveTab($tab)
    {
        $this->activeTab = $tab;
    }
}
