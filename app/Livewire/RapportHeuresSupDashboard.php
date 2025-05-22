<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Presence;
use App\Models\User;
use App\Models\Employeur;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class RapportHeuresSupDashboard extends Component
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
        // Requête de base pour les présences
        $query = Presence::query()
            ->whereBetween('date_pointage', [$this->dateDebut, $this->dateFin])
            ->where('heures_supp', '>', 0);
            
        if ($this->employeurId) {
            $query->where('employeur_id', $this->employeurId);
        }
        
        $presences = $query->get();
        
        // Total des heures supplémentaires
        $totalHeuresSupp = $presences->sum('heures_supp');
        
        // Nombre d'employés avec heures supplémentaires
        $employesAvecHeuresSupp = $presences->pluck('user_id')->unique()->count();
        
        // Moyenne d'heures supplémentaires par employé
        $moyenneHeuresSupp = $employesAvecHeuresSupp > 0 ? $totalHeuresSupp / $employesAvecHeuresSupp : 0;
        
        // Heures supplémentaires par jour
        $heuresSuppParJour = $presences
            ->groupBy(function ($presence) {
                return Carbon::parse($presence->date_pointage)->format('Y-m-d');
            })
            ->map(function ($items) {
                return $items->sum('heures_supp');
            });
        
        // Heures supplémentaires par employeur
        $heuresSuppParEmployeur = $presences
            ->groupBy('employeur_id')
            ->map(function ($items) {
                $employeur = Employeur::find($items->first()->employeur_id);
                return [
                    'nom' => $employeur ? $employeur->nom : 'Inconnu',
                    'heures' => $items->sum('heures_supp'),
                    'count' => $items->count(),
                ];
            });
        
        // Heures supplémentaires par jour de la semaine
        $heuresSuppParJourSemaine = $presences
            ->groupBy(function ($presence) {
                return Carbon::parse($presence->date_pointage)->format('w'); // 0 (dimanche) à 6 (samedi)
            })
            ->map(function ($items) {
                return $items->sum('heures_supp');
            });
        
        // Top 5 des employés avec le plus d'heures supplémentaires
        $topEmployes = $presences
            ->groupBy('user_id')
            ->map(function ($items) {
                $user = User::find($items->first()->user_id);
                return [
                    'nom' => $user ? $user->name : 'Inconnu',
                    'heures' => $items->sum('heures_supp'),
                ];
            })
            ->sortByDesc('heures')
            ->take(5);
        
        $this->stats = [
            'total_heures_supp' => $totalHeuresSupp,
            'employes_avec_heures_supp' => $employesAvecHeuresSupp,
            'moyenne_heures_supp' => $moyenneHeuresSupp,
            'heures_supp_par_jour' => $heuresSuppParJour,
            'heures_supp_par_employeur' => $heuresSuppParEmployeur,
            'heures_supp_par_jour_semaine' => $heuresSuppParJourSemaine,
            'top_employes' => $topEmployes,
        ];
        
        // Préparer les données pour les graphiques
        $this->prepareChartData();
    }

    public function prepareChartData()
    {
        // Données pour le graphique des heures supplémentaires par jour
        $labels = [];
        $data = [];
        
        // Créer un tableau de tous les jours dans la période
        $currentDate = Carbon::parse($this->dateDebut);
        $endDate = Carbon::parse($this->dateFin);
        
        while ($currentDate <= $endDate) {
            $dateKey = $currentDate->format('Y-m-d');
            $labels[] = $currentDate->format('d/m');
            $data[] = $this->stats['heures_supp_par_jour'][$dateKey] ?? 0;
            $currentDate->addDay();
        }
        
        // Données pour le graphique des heures supplémentaires par jour de la semaine
        $joursSemaine = [
            '0' => 'Dimanche',
            '1' => 'Lundi',
            '2' => 'Mardi',
            '3' => 'Mercredi',
            '4' => 'Jeudi',
            '5' => 'Vendredi',
            '6' => 'Samedi',
        ];
        
        $jourSemaineLabels = [];
        $jourSemaineData = [];
        
        for ($i = 1; $i <= 6; $i++) {
            $jourSemaineLabels[] = $joursSemaine[$i];
            $jourSemaineData[] = $this->stats['heures_supp_par_jour_semaine'][$i] ?? 0;
        }
        $jourSemaineLabels[] = $joursSemaine['0'];
        $jourSemaineData[] = $this->stats['heures_supp_par_jour_semaine']['0'] ?? 0;
        
        // Données pour le graphique des top employés
        $topEmployesLabels = [];
        $topEmployesData = [];
        
        foreach ($this->stats['top_employes'] as $employe) {
            $topEmployesLabels[] = $employe['nom'];
            $topEmployesData[] = $employe['heures'];
        }
        
        $this->chartData = [
            'heuresSuppParJour' => [
                'labels' => $labels,
                'data' => $data,
            ],
            'heuresSuppParJourSemaine' => [
                'labels' => $jourSemaineLabels,
                'data' => $jourSemaineData,
            ],
            'topEmployes' => [
                'labels' => $topEmployesLabels,
                'data' => $topEmployesData,
            ],
        ];
    }

    public function render()
    {
        return view('livewire.rapport-heures-supp-dashboard');
    }
}
