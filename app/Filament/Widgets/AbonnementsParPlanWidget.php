<?php

namespace App\Filament\Widgets;

use App\Models\Abonnement;
use App\Models\PlanAbonnement;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class AbonnementsParPlanWidget extends ChartWidget
{
    protected static ?string $heading = 'Répartition des abonnements par plan';
    protected static ?string $pollingInterval = '60s';
    protected static ?string $maxHeight = '300px';
    protected int | string | array $columnSpan = 'full';

    protected function getData(): array
    {
        // Si l'utilisateur n'est pas un super admin, ne montrer que les données de son entreprise
        if (!auth()->user()->isSuperAdmin()) {
            $entrepriseId = auth()->user()->entreprise_id;
            
            $abonnements = Abonnement::where('entreprise_id', $entrepriseId)
                ->with('planAbonnement')
                ->get()
                ->groupBy('plan_abonnement_id');
                
            $labels = [];
            $values = [];
            $colors = [];
            
            foreach ($abonnements as $planId => $abonnementsList) {
                $plan = PlanAbonnement::find($planId);
                if ($plan) {
                    $labels[] = $plan->nom;
                    $values[] = $abonnementsList->count();
                    $colors[] = $plan->couleur ?? '#' . substr(md5($plan->nom), 0, 6);
                }
            }
            
            return [
                'datasets' => [
                    [
                        'label' => 'Abonnements',
                        'data' => $values,
                        'backgroundColor' => $colors,
                    ],
                ],
                'labels' => $labels,
            ];
        }
        
        // Pour super admin - tous les abonnements actifs par plan
        $abonnements = Abonnement::where('statut', 'actif')
            ->where('date_fin', '>', now())
            ->with('planAbonnement')
            ->get()
            ->groupBy('plan_abonnement_id');
            
        $labels = [];
        $values = [];
        $colors = [];
        
        foreach ($abonnements as $planId => $abonnementsList) {
            $plan = PlanAbonnement::find($planId);
            if ($plan) {
                $labels[] = $plan->nom;
                $values[] = $abonnementsList->count();
                $colors[] = $plan->couleur ?? '#' . substr(md5($plan->nom), 0, 6);
            }
        }
        
        return [
            'datasets' => [
                [
                    'label' => 'Abonnements actifs',
                    'data' => $values,
                    'backgroundColor' => $colors,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'pie';
    }
    
    public static function canView(): bool
    {
        // Visible pour tous les utilisateurs
        return true;
    }
}
