<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Presence;
use App\Models\User;
use App\Models\Employeur;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class RapportPresenceDashboard extends Component
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
        $query = Presence::query()
            ->whereBetween('date_pointage', [$this->dateDebut, $this->dateFin]);
            
        if ($this->employeurId) {
            $query->where('employeur_id', $this->employeurId);
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
        
        // Présences par heure
        $presencesParHeure = $presences
            ->groupBy(function ($presence) {
                return Carbon::parse($presence->date_pointage)->format('H');
            })
            ->map->count();
        
        $this->stats = [
            'total' => $totalPresences,
            'par_type' => $presencesParType,
            'par_jour' => $presencesParJour,
            'par_employeur' => $presencesParEmployeur,
            'taux_presence' => $tauxPresence,
            'par_heure' => $presencesParHeure,
        ];
        
        // Préparer les données pour les graphiques
        $this->prepareChartData();
    }

    public function prepareChartData()
    {
        // Données pour le graphique des présences par jour
        $labels = [];
        $data = [];
        
        // Créer un tableau de tous les jours dans la période
        $currentDate = Carbon::parse($this->dateDebut);
        $endDate = Carbon::parse($this->dateFin);
        
        while ($currentDate <= $endDate) {
            $dateKey = $currentDate->format('Y-m-d');
            $labels[] = $currentDate->format('d/m');
            $data[] = $this->stats['par_jour'][$dateKey] ?? 0;
            $currentDate->addDay();
        }
        
        $this->chartData = [
            'presencesParJour' => [
                'labels' => $labels,
                'data' => $data,
            ],
            'presencesParType' => [
                'labels' => array_keys($this->stats['par_type']->toArray()),
                'data' => array_values($this->stats['par_type']->toArray()),
            ],
            'presencesParHeure' => [
                'labels' => array_keys($this->stats['par_heure']->toArray()),
                'data' => array_values($this->stats['par_heure']->toArray()),
            ],
        ];
    }

    public function render()
    {
        return view('livewire.rapport-presence-dashboard');
    }
}
