<?php

namespace App\Filament\Widgets;

use App\Models\Abonnement;
use App\Models\Facturation;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AbonnementsRevenueWidget extends ChartWidget
{
    protected static ?string $heading = 'Évolution des revenus d\'abonnements';
    protected static ?string $pollingInterval = '60s';
    protected int | string | array $columnSpan = 'full';
    protected static ?string $maxHeight = '300px';

    protected function getData(): array
    {
        // Ce widget n'est visible que pour les super admins
        if (!auth()->user()->isSuperAdmin()) {
            return [
                'datasets' => [
                    [
                        'label' => 'Revenus mensuels',
                        'data' => [],
                    ],
                ],
                'labels' => [],
            ];
        }
        
        // Récupérer les données des 12 derniers mois
        $derniersMois = [];
        $labels = [];
        
        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $derniersMois[] = [
                'year' => $date->year,
                'month' => $date->month,
            ];
            $labels[] = $date->format('M Y');
        }
        
        // Récupérer les revenus mensuels (nouveaux abonnements)
        $revenus = [];
        $revenusRenouvellement = [];
        
        foreach ($derniersMois as $mois) {
            $revenuMensuel = Abonnement::whereYear('created_at', $mois['year'])
                ->whereMonth('created_at', $mois['month'])
                ->sum('montant');
                
            $revenus[] = $revenuMensuel;
            
            // Revenus de renouvellement (basés sur les dates de début après la création)
            $revenuRenouvellement = Abonnement::whereYear('date_debut', $mois['year'])
                ->whereMonth('date_debut', $mois['month'])
                ->where('created_at', '<', DB::raw('date_debut')) // Seulement les renouvellements
                ->sum('montant');
                
            $revenusRenouvellement[] = $revenuRenouvellement;
        }
        
        return [
            'datasets' => [
                [
                    'label' => 'Nouveaux abonnements',
                    'data' => $revenus,
                    'backgroundColor' => 'rgba(59, 130, 246, 0.5)', // Bleu
                    'borderColor' => 'rgb(59, 130, 246)',
                    'borderWidth' => 1,
                ],
                [
                    'label' => 'Renouvellements',
                    'data' => $revenusRenouvellement,
                    'backgroundColor' => 'rgba(16, 185, 129, 0.5)', // Vert
                    'borderColor' => 'rgb(16, 185, 129)',
                    'borderWidth' => 1,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
    
    protected function getOptions(): array
    {
        return [
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'callback' => '(value) => value + " XOF"',
                    ],
                ],
            ],
            'elements' => [
                'line' => [
                    'tension' => 0.3, // Légère courbe pour les lignes
                ],
            ],
            'plugins' => [
                'tooltip' => [
                    'callbacks' => [
                        'label' => '(context) => context.dataset.label + ": " + context.parsed.y + " XOF"',
                    ],
                ],
            ],
        ];
    }
    
    public static function canView(): bool
    {
        // Visible uniquement pour les super admins
        return auth()->user()->isSuperAdmin();
    }
}
