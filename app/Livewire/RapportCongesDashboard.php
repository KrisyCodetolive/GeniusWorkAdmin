<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Conge;
use App\Models\User;
use App\Models\Employeur;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class RapportCongesDashboard extends Component
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
        // Requête de base pour les congés
        $query = Conge::query()
            ->where(function($q) {
                $q->whereBetween('date_debut', [$this->dateDebut, $this->dateFin])
                  ->orWhereBetween('date_fin', [$this->dateDebut, $this->dateFin])
                  ->orWhere(function($q2) {
                      $q2->where('date_debut', '<=', $this->dateDebut)
                         ->where('date_fin', '>=', $this->dateFin);
                  });
            });
            
        if ($this->employeurId) {
            $query->whereHas('user', function($q) {
                $q->whereHas('employeurs', function($q2) {
                    $q2->where('employeur_id', $this->employeurId);
                });
            });
        }
        
        $conges = $query->get();
        
        // Total des congés
        $totalConges = $conges->count();
        
        // Nombre de jours de congés
        $totalJoursConges = $conges->sum(function($conge) {
            return Carbon::parse($conge->date_debut)->diffInDays(Carbon::parse($conge->date_fin)) + 1;
        });
        
        // Nombre d'employés en congé
        $employesEnConge = $conges->pluck('user_id')->unique()->count();
        
        // Congés par type
        $congesParType = $conges->groupBy('type_conge')->map->count();
        
        // Congés par statut
        $congesParStatut = $conges->groupBy('statut')->map->count();
        
        // Congés par mois
        $congesParMois = $conges->groupBy(function ($conge) {
            return Carbon::parse($conge->date_debut)->format('Y-m');
        })->map->count();
        
        // Durée moyenne des congés
        $dureeMoyenneConges = $totalConges > 0 ? $totalJoursConges / $totalConges : 0;
        
        // Top 5 des employés avec le plus de jours de congés
        $topEmployes = $conges
            ->groupBy('user_id')
            ->map(function ($items) {
                $user = User::find($items->first()->user_id);
                $jours = $items->sum(function($conge) {
                    return Carbon::parse($conge->date_debut)->diffInDays(Carbon::parse($conge->date_fin)) + 1;
                });
                return [
                    'nom' => $user ? $user->name : 'Inconnu',
                    'jours' => $jours,
                ];
            })
            ->sortByDesc('jours')
            ->take(5);
        
        // Congés par employeur
        $congesParEmployeur = collect();
        
        if (!$this->employeurId) {
            $employeurIds = User::whereIn('id', $conges->pluck('user_id')->unique())
                ->with('employeurs')
                ->get()
                ->pluck('employeurs.*.id')
                ->flatten()
                ->unique();
            
            foreach ($employeurIds as $empId) {
                $employeur = Employeur::find($empId);
                if ($employeur) {
                    $congesEmployeur = $conges->filter(function($conge) use ($empId) {
                        return $conge->user && $conge->user->employeurs->contains('id', $empId);
                    });
                    
                    $congesParEmployeur->put($empId, [
                        'nom' => $employeur->nom,
                        'count' => $congesEmployeur->count(),
                        'jours' => $congesEmployeur->sum(function($conge) {
                            return Carbon::parse($conge->date_debut)->diffInDays(Carbon::parse($conge->date_fin)) + 1;
                        }),
                    ]);
                }
            }
        }
        
        $this->stats = [
            'total_conges' => $totalConges,
            'total_jours_conges' => $totalJoursConges,
            'employes_en_conge' => $employesEnConge,
            'conges_par_type' => $congesParType,
            'conges_par_statut' => $congesParStatut,
            'conges_par_mois' => $congesParMois,
            'duree_moyenne_conges' => $dureeMoyenneConges,
            'top_employes' => $topEmployes,
            'conges_par_employeur' => $congesParEmployeur,
        ];
        
        // Préparer les données pour les graphiques
        $this->prepareChartData();
    }

    public function prepareChartData()
    {
        // Données pour le graphique des congés par mois
        $labels = [];
        $data = [];
        
        // Créer un tableau de tous les mois dans la période
        $currentDate = Carbon::parse($this->dateDebut)->startOfMonth();
        $endDate = Carbon::parse($this->dateFin)->startOfMonth();
        
        while ($currentDate <= $endDate) {
            $dateKey = $currentDate->format('Y-m');
            $labels[] = $currentDate->format('M Y');
            $data[] = $this->stats['conges_par_mois'][$dateKey] ?? 0;
            $currentDate->addMonth();
        }
        
        // Données pour le graphique des congés par type
        $typeLabels = [];
        $typeData = [];
        
        foreach ($this->stats['conges_par_type'] as $type => $count) {
            $typeLabels[] = $type ?: 'Non spécifié';
            $typeData[] = $count;
        }
        
        // Données pour le graphique des congés par statut
        $statutLabels = [];
        $statutData = [];
        
        foreach ($this->stats['conges_par_statut'] as $statut => $count) {
            $statutLabels[] = $statut ?: 'Non spécifié';
            $statutData[] = $count;
        }
        
        // Données pour le graphique des top employés
        $topEmployesLabels = [];
        $topEmployesData = [];
        
        foreach ($this->stats['top_employes'] as $employe) {
            $topEmployesLabels[] = $employe['nom'];
            $topEmployesData[] = $employe['jours'];
        }
        
        $this->chartData = [
            'congesParMois' => [
                'labels' => $labels,
                'data' => $data,
            ],
            'congesParType' => [
                'labels' => $typeLabels,
                'data' => $typeData,
            ],
            'congesParStatut' => [
                'labels' => $statutLabels,
                'data' => $statutData,
            ],
            'topEmployes' => [
                'labels' => $topEmployesLabels,
                'data' => $topEmployesData,
            ],
        ];
    }

    public function render()
    {
        return view('livewire.rapport-conges-dashboard');
    }
}
