<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Presence;
use App\Models\User;
use App\Models\Employeur;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use App\Services\RetardAbsenceService;

class RapportRetardsDashboard extends Component
{
    public $dateDebut;
    public $dateFin;
    public $employeurId;
    
    public $stats;
    public $chartData;

    public function mount($dateDebut, $dateFin, $employeurId = null)
    {
        $this->dateDebut = $dateDebut;
        $this->dateFin = $dateFin;
        $this->employeurId = $employeurId;
        
        $this->loadStats();
    }

    public function loadStats()
    {
        // Utiliser le service RetardAbsenceService pour obtenir des données précises
        $retardService = app(RetardAbsenceService::class);
        
        // Requête de base pour les présences
        $query = Presence::query()
            ->whereBetween('date_pointage', [$this->dateDebut, $this->dateFin]);
            
        if ($this->employeurId) {
            $query->where('employeur_id', $this->employeurId);
        }
        
        $presences = $query->get();
        
        // Retards
        $retards = $presences->where('est_retard', true);
        $totalRetards = $retards->count();
        
        // Absences (utilisateurs qui n'ont pas pointé)
        // Dans un cas réel, nous utiliserions le service RetardAbsenceService pour obtenir les absences
        $utilisateursPresents = $presences
            ->where('type_pointage', 'entree')
            ->pluck('user_id')
            ->unique();
        
        // Obtenir tous les utilisateurs qui devraient être présents
        $query = User::query();
        if ($this->employeurId) {
            $query->whereHas('employeurs', function($q) {
                $q->where('employeur_id', $this->employeurId);
            });
        }
        $totalUtilisateurs = $query->count();
        $totalAbsences = $totalUtilisateurs - $utilisateursPresents->count();
        if ($totalAbsences < 0) $totalAbsences = 0;
        
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
        
        // Retards par plage horaire
        $retardsParPlage = $retards
            ->groupBy(function ($presence) {
                $minutes = Carbon::parse($presence->date_pointage)->format('H') * 60 + 
                           Carbon::parse($presence->date_pointage)->format('i');
                if ($minutes < 540) return 'avant_9h'; // Avant 9h
                if ($minutes < 720) return '9h_12h';   // 9h-12h
                if ($minutes < 840) return '12h_14h';  // 12h-14h
                if ($minutes < 1020) return '14h_17h'; // 14h-17h
                return 'apres_17h';                    // Après 17h
            })
            ->map->count();
        
        // Distribution des retards par durée
        $retardsParDuree = [
            'moins_15min' => $retards->filter(function ($retard) {
                return $retard->minutes_retard <= 15;
            })->count(),
            '15_30min' => $retards->filter(function ($retard) {
                return $retard->minutes_retard > 15 && $retard->minutes_retard <= 30;
            })->count(),
            '30_60min' => $retards->filter(function ($retard) {
                return $retard->minutes_retard > 30 && $retard->minutes_retard <= 60;
            })->count(),
            'plus_60min' => $retards->filter(function ($retard) {
                return $retard->minutes_retard > 60;
            })->count(),
        ];
        
        $this->stats = [
            'total_retards' => $totalRetards,
            'total_absences' => $totalAbsences,
            'retards_par_jour' => $retardsParJour,
            'retards_par_employeur' => $retardsParEmployeur,
            'duree_moyenne_retard' => $moyenneRetards,
            'retards_par_plage' => $retardsParPlage,
            'retards_par_duree' => $retardsParDuree,
        ];
        
        // Préparer les données pour les graphiques
        $this->prepareChartData();
    }

    public function prepareChartData()
    {
        // Données pour le graphique des retards par jour
        $labels = [];
        $data = [];
        
        // Créer un tableau de tous les jours dans la période
        $currentDate = Carbon::parse($this->dateDebut);
        $endDate = Carbon::parse($this->dateFin);
        
        while ($currentDate <= $endDate) {
            $dateKey = $currentDate->format('Y-m-d');
            $labels[] = $currentDate->format('d/m');
            $data[] = $this->stats['retards_par_jour'][$dateKey] ?? 0;
            $currentDate->addDay();
        }
        
        // Données pour le graphique des retards par plage horaire
        $plageLabels = [
            'avant_9h' => 'Avant 9h',
            '9h_12h' => '9h-12h',
            '12h_14h' => '12h-14h',
            '14h_17h' => '14h-17h',
            'apres_17h' => 'Après 17h',
        ];
        
        $plageData = [];
        foreach ($plageLabels as $key => $label) {
            $plageData[] = $this->stats['retards_par_plage'][$key] ?? 0;
        }
        
        // Données pour le graphique des retards par durée
        $dureeLabels = [
            'moins_15min' => 'Moins de 15 min',
            '15_30min' => '15-30 min',
            '30_60min' => '30-60 min',
            'plus_60min' => 'Plus de 60 min',
        ];
        
        $dureeData = [];
        foreach ($dureeLabels as $key => $label) {
            $dureeData[] = $this->stats['retards_par_duree'][$key] ?? 0;
        }
        
        $this->chartData = [
            'retardsParJour' => [
                'labels' => $labels,
                'data' => $data,
            ],
            'retardsParPlage' => [
                'labels' => array_values($plageLabels),
                'data' => $plageData,
            ],
            'retardsParDuree' => [
                'labels' => array_values($dureeLabels),
                'data' => $dureeData,
            ],
        ];
    }

    public function render()
    {
        return view('livewire.rapport-retards-dashboard');
    }
}
