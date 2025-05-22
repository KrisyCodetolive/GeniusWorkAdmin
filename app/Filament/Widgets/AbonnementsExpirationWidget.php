<?php

namespace App\Filament\Widgets;

use App\Models\Abonnement;
use Filament\Widgets\ChartWidget;
use Carbon\Carbon;

class AbonnementsExpirationWidget extends ChartWidget
{
    protected static ?string $heading = 'Prévisions d\'expiration des abonnements';
    protected static ?string $pollingInterval = '60s';
    protected int | string | array $columnSpan = 'full';

    protected function getData(): array
    {
        $query = Abonnement::where('statut', 'actif')
            ->where('date_fin', '>', now());
            
        // Si l'utilisateur n'est pas un super admin, ne montrer que les données de son entreprise
        if (!auth()->user()->isSuperAdmin()) {
            $query->where('entreprise_id', auth()->user()->entreprise_id);
        }
        
        // Grouper les abonnements par période d'expiration
        $expirationCounts = [
            '7j' => 0,  // 7 jours
            '30j' => 0, // 30 jours
            '90j' => 0, // 90 jours
            '180j' => 0, // 180 jours
            '365j' => 0, // 365 jours
            '+365j' => 0, // Plus de 365 jours
        ];
        
        $abonnements = $query->get();
        
        foreach ($abonnements as $abonnement) {
            $joursRestants = now()->diffInDays($abonnement->date_fin, false);
            
            if ($joursRestants <= 7) {
                $expirationCounts['7j']++;
            } elseif ($joursRestants <= 30) {
                $expirationCounts['30j']++;
            } elseif ($joursRestants <= 90) {
                $expirationCounts['90j']++;
            } elseif ($joursRestants <= 180) {
                $expirationCounts['180j']++;
            } elseif ($joursRestants <= 365) {
                $expirationCounts['365j']++;
            } else {
                $expirationCounts['+365j']++;
            }
        }
        
        return [
            'datasets' => [
                [
                    'label' => 'Nombre d\'abonnements',
                    'data' => array_values($expirationCounts),
                    'backgroundColor' => [
                        'rgba(239, 68, 68, 0.7)', // Rouge pour 7j
                        'rgba(249, 115, 22, 0.7)', // Orange pour 30j
                        'rgba(245, 158, 11, 0.7)', // Jaune pour 90j
                        'rgba(16, 185, 129, 0.7)', // Vert pour 180j
                        'rgba(59, 130, 246, 0.7)', // Bleu pour 365j
                        'rgba(99, 102, 241, 0.7)', // Indigo pour +365j
                    ],
                    'borderColor' => [
                        'rgb(239, 68, 68)',
                        'rgb(249, 115, 22)',
                        'rgb(245, 158, 11)',
                        'rgb(16, 185, 129)',
                        'rgb(59, 130, 246)',
                        'rgb(99, 102, 241)',
                    ],
                    'borderWidth' => 1
                ],
            ],
            'labels' => [
                '7 jours',
                '30 jours',
                '90 jours',
                '180 jours',
                '365 jours',
                '+365 jours',
            ],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
    
    protected function getOptions(): array
    {
        return [
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'precision' => 0,
                    ],
                ],
            ],
        ];
    }
}
